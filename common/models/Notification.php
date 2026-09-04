<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

class Notification extends ActiveRecord
{
    public const TYPE_INFO = 'info';
    public const TYPE_WARNING = 'warning';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_DANGER = 'danger';

    public static function tableName(): string
    {
        return '{{%notification}}';
    }

    public function rules(): array
    {
        return [
            [['title', 'message'], 'required'],
            [['message'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['user_id', 'is_read'], 'integer'],
            [['type'], 'in', 'range' => [self::TYPE_INFO, self::TYPE_WARNING, self::TYPE_SUCCESS, self::TYPE_DANGER]],
            [['type'], 'default', 'value' => self::TYPE_INFO],
            [['created_at'], 'integer'],
        ];
    }
}
