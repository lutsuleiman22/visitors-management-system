<?php

declare(strict_types=1);

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Blacklist model
 *
 * @property int $id
 * @property string|null $national_id
 * @property string|null $phone_number
 * @property string|null $reason
 * @property int $status
 * @property int|null $created_at
 */
class Blacklist extends ActiveRecord
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    public static function tableName(): string
    {
        return '{{%blacklists}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['reason'], 'string'],
            [['national_id', 'phone_number'], 'string', 'max' => 255],
            [['status', 'created_at'], 'integer'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['national_id', 'phone_number', 'reason'], 'trim'],
            [['national_id', 'phone_number'], 'validateIdentityPresent'],
        ];
    }

    public function validateIdentityPresent(string $attribute): void
    {
        $hasNationalId = $this->national_id !== null && $this->national_id !== '';
        $hasPhone = $this->phone_number !== null && $this->phone_number !== '';

        if (!$hasNationalId && !$hasPhone) {
            $this->addError($attribute, 'Provide at least a National ID or Phone Number.');
        }
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'national_id' => 'National ID',
            'phone_number' => 'Phone Number',
            'reason' => 'Reason',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }

    public static function isBlocked(string $nationalId, string $phoneNumber): bool
    {
        return self::findActiveMatch($nationalId, $phoneNumber) !== null;
    }

    public static function findActiveMatch(string $nationalId, string $phoneNumber): self|null
    {
        $conditions = ['or'];
        if ($nationalId !== '') {
            $conditions[] = ['national_id' => $nationalId];
        }
        if ($phoneNumber !== '') {
            $conditions[] = ['phone_number' => $phoneNumber];
        }
        if (count($conditions) === 1) {
            return null;
        }

        return self::find()
            ->where(['status' => self::STATUS_ACTIVE])
            ->andWhere($conditions)
            ->one();
    }
}
