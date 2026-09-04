<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

class AuditLog extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%audit_log}}';
    }

    public function rules(): array
    {
        return [
            [['action', 'description', 'created_at'], 'required'],
            [['user_id'], 'integer'],
            [['description'], 'string'],
            [['action'], 'string', 'max' => 80],
            [['ip_address'], 'string', 'max' => 45],
            [['created_at'], 'safe'],
        ];
    }
}
