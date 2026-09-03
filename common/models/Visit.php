<?php

declare(strict_types=1);

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Visit model
 *
 * @property int $id
 * @property int $visitor_id
 * @property int|null $host_user_id
 * @property string|null $purpose
 * @property string|null $from_location
 * @property string|null $destination
 * @property string|null $visitor_pass_number
 * @property string|null $signature_path
 * @property string|null $qr_code_hash
 * @property string $status
 * @property string|null $check_in_time
 * @property string|null $check_out_time
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Visitor $visitor
 * @property User|null $host
 */
class Visit extends ActiveRecord
{
    public const STATUS_CHECKED_IN = 'Checked-In';
    public const STATUS_CHECKED_OUT = 'Checked-Out';

    public static function tableName(): string
    {
        return '{{%visits}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['visitor_id'], 'required'],
            [['visitor_id', 'host_user_id', 'created_at', 'updated_at'], 'integer'],
            [['purpose', 'from_location', 'destination', 'qr_code_hash', 'status', 'signature_path'], 'string', 'max' => 255],
            [['visitor_pass_number'], 'string', 'max' => 32],
            [['visitor_pass_number'], 'unique'],
            [['check_in_time', 'check_out_time'], 'safe'],
            [['status'], 'default', 'value' => self::STATUS_CHECKED_IN],
            [['status'], 'in', 'range' => array_keys(self::statusList())],
            [['purpose', 'from_location', 'destination', 'qr_code_hash', 'visitor_pass_number'], 'trim'],
            [
                ['visitor_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Visitor::class,
                'targetAttribute' => ['visitor_id' => 'id'],
            ],
            [
                ['host_user_id'],
                'exist',
                'skipOnEmpty' => true,
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['host_user_id' => 'id'],
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'visitor_id' => 'Visitor',
            'host_user_id' => 'Host',
            'purpose' => 'Purpose of Visit',
            'from_location' => 'Coming From',
            'destination' => 'Destination',
            'visitor_pass_number' => 'Visitor Pass Number',
            'signature_path' => 'Signature',
            'qr_code_hash' => 'QR Code',
            'status' => 'Status',
            'check_in_time' => 'Check-In Time',
            'check_out_time' => 'Check-Out Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusList(): array
    {
        return [
            self::STATUS_CHECKED_IN => 'Checked-In',
            self::STATUS_CHECKED_OUT => 'Checked-Out',
        ];
    }

    public function getVisitor(): ActiveQuery
    {
        return $this->hasOne(Visitor::class, ['id' => 'visitor_id']);
    }

    public function getHost(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'host_user_id']);
    }

    public function isCheckedIn(): bool
    {
        return $this->status === self::STATUS_CHECKED_IN && $this->check_out_time === null;
    }

    public static function generateQrCodeHash(): string
    {
        do {
            $hash = Yii::$app->security->generateRandomString(48);
        } while (self::find()->where(['qr_code_hash' => $hash])->exists());

        return $hash;
    }

    /**
     * Unique badge ID, e.g. VIS-8492.
     */
    public static function generatePassNumber(): string
    {
        do {
            $number = 'VIS-' . random_int(1000, 9999);
        } while (self::find()->where(['visitor_pass_number' => $number])->exists());

        return $number;
    }

    public function checkOut(): bool
    {
        if (!$this->isCheckedIn()) {
            return false;
        }

        $this->status = self::STATUS_CHECKED_OUT;
        $this->check_out_time = date('Y-m-d H:i:s');

        return $this->save(false);
    }
}
