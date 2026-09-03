<?php

declare(strict_types=1);

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Visitor model
 *
 * @property int $id
 * @property string $full_name
 * @property string $phone_number
 * @property string|null $national_id
 * @property string|null $photo_path
 * @property int $status
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Visit[] $visits
 */
class Visitor extends ActiveRecord
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    public static function tableName(): string
    {
        return '{{%visitors}}';
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
            [['full_name', 'phone_number'], 'required'],
            [['full_name', 'phone_number', 'national_id', 'photo_path'], 'string', 'max' => 255],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['full_name', 'phone_number', 'national_id'], 'trim'],
            [
                ['phone_number'],
                'match',
                'pattern' => '/^[0-9+\-\s()]{7,30}$/',
                'message' => 'Please enter a valid phone number.',
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'full_name' => 'Full Name',
            'phone_number' => 'Phone Number',
            'national_id' => 'National ID',
            'photo_path' => 'Photo',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getVisits(): ActiveQuery
    {
        return $this->hasMany(Visit::class, ['visitor_id' => 'id']);
    }
}
