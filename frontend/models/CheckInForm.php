<?php

declare(strict_types=1);

namespace frontend\models;

use common\models\Blacklist;
use common\models\User;
use common\models\Visit;
use common\models\Visitor;
use common\services\AuditLogService;
use common\services\NotificationService;
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
    public string $gender = '';
    public string $national_id = '';
    public string $origin = '';
    public string $destination = '';
    public string $purpose = '';
    public ?int $host_user_id = null;
    public string $host_name = '';
    public string $signature_data = '';

    public function rules(): array
    {
        return [
            [['full_name', 'phone_number', 'gender', 'origin', 'host_name', 'signature_data'], 'required'],
            [['full_name', 'phone_number', 'national_id', 'origin', 'destination', 'purpose', 'gender', 'host_name'], 'string', 'max' => 255],
            [['host_user_id'], 'integer'],
            [['host_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['host_user_id' => 'id']],
            [['signature_data'], 'string'],
            [
                ['phone_number'],
                'match',
                'pattern' => '/^[0-9+\-\s()]{7,30}$/',
                'message' => 'Please enter a valid phone number.',
            ],
            [['gender'], 'in', 'range' => ['Male', 'Female', 'Other']],
            [['full_name', 'phone_number', 'national_id', 'origin', 'destination', 'purpose', 'gender', 'host_name'], 'trim'],
            [['national_id', 'phone_number'], 'validateNotBlacklisted'],
            [['signature_data'], 'validateSignatureData'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'full_name' => 'Full Name',
            'phone_number' => 'Phone Number',
            'gender' => 'Gender',
            'national_id' => 'National ID',
            'origin' => 'Origin / Where Coming From',
            'destination' => 'Destination',
            'host_user_id' => 'Host',
            'host_name' => 'Host',
            'purpose' => 'Purpose of Visit',
            'signature_data' => 'Signature',
        ];
    }

    /** @return array<int, string> */
    public static function hostList(): array
    {
        return ArrayHelper::map(
            User::find()
                ->where(['status' => User::STATUS_ACTIVE])
                ->orderBy(['username' => SORT_ASC])
                ->all(),
            'id',
            'username',
        );
    }

    public function validateNotBlacklisted(string $attribute): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $nationalId = trim((string) $this->national_id);
        if (Blacklist::isBlocked($nationalId, $this->phone_number)) {
            $match = Blacklist::findActiveMatch($nationalId, $this->phone_number);
            $reason = $match !== null && $match->reason ? $match->reason : 'Security restriction';
            NotificationService::createNotification('Security alert: blacklisted check-in attempt.', 'danger');
            $this->addError($attribute, 'Check-in denied. This visitor is blacklisted. Reason: ' . $reason);
        }
    }

    public function validateSignatureData(string $attribute): void
    {
        if ($this->signature_data === '') {
            $this->addError($attribute, 'Please sign in the signature pad before submitting.');
            return;
        }

        if (!preg_match('#^data:image/(png|jpeg|jpg);base64,#i', $this->signature_data)) {
            $this->addError($attribute, 'Please sign in the signature pad before submitting.');
        }
    }

    public function process(): Visit|null
    {
        if (!$this->validate()) {
            return null;
        }

        $transaction = null;

        try {
            $matchedHost = null;
            $hostName = trim((string) $this->host_name);
            if ($hostName !== '') {
                $matchedHost = User::find()->where(['username' => $hostName, 'status' => User::STATUS_ACTIVE])->one();
            }
            if ($matchedHost !== null) {
                $this->host_user_id = (int) $matchedHost->id;
            }

            $transaction = Yii::$app->db->beginTransaction();
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

            if (!$visitor->save()) {
                Yii::error(['visitorSaveErrors' => $visitor->getErrors(), 'attributes' => $visitor->attributes], __METHOD__);
                $this->addErrors($visitor->getErrors());
                $transaction->rollBack();
                return null;
            }

            $signaturePath = $this->saveSignature();
            if ($signaturePath === null) {
                $this->addError('signature_data', 'Unable to save signature. Please verify directory permissions.');
                $transaction->rollBack();
                return null;
            }

            $visit = new Visit();
            $visit->visitor_id = (int) $visitor->id;
            $visit->host_user_id = $this->host_user_id ?: null;
            $visit->purpose = $this->purpose;
            $visit->from_location = $this->origin;
            $visit->destination = $this->destination;
            $visit->visitor_pass_number = Visit::generatePassNumber();
            $visit->signature_path = $signaturePath;
            $visit->qr_code_hash = Visit::generateQrCodeHash();
            $visit->status = Visit::STATUS_CHECKED_IN;
            $visit->check_in_time = date('Y-m-d H:i:s');

            if (!$visit->save()) {
                Yii::error(['visitSaveErrors' => $visit->getErrors(), 'attributes' => $visit->attributes], __METHOD__);
                $this->addErrors($visit->getErrors());
                $transaction->rollBack();
                return null;
            }

            $transaction->commit();
            AuditLogService::logAction('check-in', 'Visitor checked in: ' . $visitor->full_name . ' (gender: ' . $this->gender . ')');
            NotificationService::createNotification('Visitor checked in: ' . $visitor->full_name, 'success');
            return $visit;
        } catch (\Throwable $e) {
            if ($transaction !== null && $transaction->getIsActive()) {
                $transaction->rollBack();
            }
            Yii::error($e->getMessage(), __METHOD__);
            $this->addError('full_name', 'Check-in Error: ' . $e->getMessage());
            return null;
        }
    }

    private function saveSignature(): string|null
    {
        return $this->saveBase64Image($this->signature_data, 'uploads/signatures', 'sign');
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

        try {
            $absoluteDir = Yii::getAlias('@frontend/web/' . $relativeDir);
            FileHelper::createDirectory($absoluteDir, 0775);

            if (!is_dir($absoluteDir) || !is_writable($absoluteDir)) {
                throw new \RuntimeException('Signature directory is not writable: ' . $absoluteDir);
            }

            $filename = $prefix . '_' . date('Ymd_His') . '_' . Yii::$app->security->generateRandomString(8) . '.' . $extension;
            $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $filename;

            if (file_put_contents($absolutePath, $binary, LOCK_EX) === false) {
                throw new \RuntimeException('Unable to write signature file.');
            }
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return null;
        }

        return $relativeDir . '/' . $filename;
    }
}