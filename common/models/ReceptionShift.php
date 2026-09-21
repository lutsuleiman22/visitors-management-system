<?php

declare(strict_types=1);

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class ReceptionShift extends ActiveRecord
{
    public const STATUS_OPEN = 1;
    public const STATUS_CLOSED = 0;

    public static function tableName(): string
    {
        return '{{%reception_shifts}}';
    }

    public function behaviors(): array
    {
        return [TimestampBehavior::class];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'status'], 'integer'],
            [['branch_code'], 'string', 'max' => 100],
            [['started_at', 'stopped_at'], 'safe'],
            [['user_id', 'branch_code', 'started_at'], 'required'],
            [['status'], 'default', 'value' => self::STATUS_OPEN],
        ];
    }
}