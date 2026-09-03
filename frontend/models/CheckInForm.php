<?php

declare(strict_types=1);

namespace frontend\models;

use common\models\Blacklist;
use common\models\User;
use common\models\Visit;
use common\models\Visitor;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Visitor self check-in form.
 */
class CheckInForm extends Model
{
    public string $full_name = '';
    public string $phone_number = '';
    public string $national_id = '';
    public string $from_location = '';
    public string $destination = '';
    public string $purpose = '';
    public ?int $host_user_id = null;
    /** Base64 data URL from signature canvas (data:image/png;base64,...) */
    public string $signature_data = '';
    /** Base64 data URL from camera webcam (data:image/jpeg;base64,...) */
    public string $photo_data = '';

    public function rules(): array
    {
        return [
            [['full_name', 'phone_number', 'from_location', 'destination', 'purpose', 'signature_data'], 'required'],
            [['full_name', 'phone_number', 'national_id', 'from_location', 'destination', 'purpose'], 'string', 'max' => 255],
            [['signature_data', 'photo_data'], 'string'],
            [['host_user_id'], 'integer'],
            [['host_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['host_user_id' => 'id']],
            [
                ['phone_number'],
                'match',
                'pattern' => '/^[0-9+\-\s()]{7,30}$/',
                'message' => 'Please enter a valid phone number.',
            ],
            [['full_name', 'phone_number', 'national_id', 'from_location', 'destination', 'purpose'], 'trim'],
            [['national_id', 'phone_number'], 'validateNotBlacklisted'],
            [['signature_data'], 'validateSignatureData'],
            [['photo_data'], 'validatePhotoData'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'full_name' => 'Full Name',
            'phone_number' => 'Phone Number',
            'national_id' => 'National ID (optional)',
            'from_location' => 'Coming From (Location / Company)',
            'destination' => 'Destination (Department / Room)',
            'purpose' => 'Purpose of Visit',
            'host_user_id' => 'Host (Staff Member)',
            'signature_data' => 'Signature',
            'photo_data' => 'Visitor Photo',
        ];
    }

    /**
     * Inarudisha orodha ya ma-host (Users) kwa ajili ya dropdown list
     */
    public static function hostList(): array
    {
        return ArrayHelper::map(
            User::find()->where(['status' => User::STATUS_ACTIVE])->all(),
            'id',
            'username'
        );
    }

    public function validateNotBlacklisted(string $attribute): void
    {
        if ($this->hasErrors()) {
            return;
        }

        if (Blacklist::isBlocked($this->national_id, $this->phone_number)) {
            $match = Blacklist::findActiveMatch($this->national_id, $this->phone_number);
            $reason = $match !== null && $match->reason ? $match->reason : 'Security restriction';
            $this->addError($attribute, 'Check-in denied. This visitor is blacklisted. Reason: ' . $reason);
        }
    }

    public function validateSignatureData(string $attribute): void
    {
        if ($this->signature_data === '') {
            return;
        }

        if (!preg_match('#^data:image/(png|jpeg|jpg);base64,#i', $this->signature_data)) {
            $this->addError($attribute, 'Please sign in the signature pad before submitting.');
        }
    }

    public function validatePhotoData(string $attribute): void
    {
        if ($this->photo_data === '') {
            return;
        }

        if (!preg_match('#^data:image/(png|jpeg|jpg);base64,#i', $this->photo_data)) {
            $this->addError($attribute, 'Invalid photo format provided.');
        }
    }

    public function process(): Visit|null
    {
        if (!$this->validate()) {
            return null;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $visitor = null;
            if ($this->national_id !== '') {
                $visitor = Visitor::findOne(['national_id' => $this->national_id]);
            }
            if ($visitor === null) {
                $visitor = new Visitor();
                $visitor->national_id = $this->national_id !== '' ? $this->national_id : null;
            }

            $visitor->full_name = $this->full_name;
            $visitor->phone_number = $this->phone_number;
            $visitor->status = Visitor::STATUS_ACTIVE;

            // Hifadhi picha ikiwa picha imepigwa
            $photoPath = $this->savePhoto();
            if ($photoPath !== null) {
                $visitor->photo_path = $photoPath;
            } else {
                $visitor->photo_path = $visitor->photo_path ?: null;
            }

            if (!$visitor->save()) {
                $this->addErrors($visitor->getErrors());
                $transaction->rollBack();
                return null;
            }

            $signaturePath = $this->saveSignature();
            if ($signaturePath === null) {
                $this->addError('signature_data', 'Unable to save signature. Please try again.');
                $transaction->rollBack();
                return null;
            }

            $visit = new Visit();
            $visit->visitor_id = (int) $visitor->id;
            $visit->host_user_id = $this->host_user_id;
            $visit->purpose = $this->purpose;
            $visit->from_location = $this->from_location;
            $visit->destination = $this->destination;
            $visit->visitor_pass_number = Visit::generatePassNumber();
            $visit->signature_path = $signaturePath;
            $visit->qr_code_hash = Visit::generateQrCodeHash();
            $visit->status = Visit::STATUS_CHECKED_IN;
            $visit->check_in_time = date('Y-m-d H:i:s');

            if (!$visit->save()) {
                $this->addErrors($visit->getErrors());
                $transaction->rollBack();
                return null;
            }

            $transaction->commit();
            return $visit;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            $this->addError('full_name', 'An unexpected error occurred during check-in.');
            return null;
        }
    }

    private function saveSignature(): string|null
    {
        return $this->saveBase64Image($this->signature_data, 'uploads/signatures', 'sign');
    }

    private function savePhoto(): string|null
    {
        return $this->saveBase64Image($this->photo_data, 'uploads/photos', 'photo');
    }

    private function saveBase64Image(string $base64Data, string $relativeDir, string $prefix): string|null
    {
        if (!preg_match('#^data:image/(png|jpeg|jpg);base64,(.+)$#i', $base64Data, $matches)) {
            return null;
        }

        $extension = strtolower($matches[1]) === 'png' ? 'png' : 'jpg';
        $binary = base64_decode($matches[2], true);
        if ($binary === false || $binary === '') {
            return null;
        }

        $absoluteDir = Yii::getAlias('@frontend/web/' . $relativeDir);
        FileHelper::createDirectory($absoluteDir, 0775);

        $filename = $prefix . '_' . date('Ymd_His') . '_' . Yii::$app->security->generateRandomString(8) . '.' . $extension;
        $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $filename;

        if (file_put_contents($absolutePath, $binary) === false) {
            return null;
        }

        return $relativeDir . '/' . $filename;
    }
}