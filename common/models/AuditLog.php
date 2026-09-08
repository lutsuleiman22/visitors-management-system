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
            [['user_id', 'record_id'], 'integer'],
            [['description'], 'string'],
            [['action', 'model'], 'string', 'max' => 80],
            [['ip_address'], 'string', 'max' => 45],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User',
            'action' => 'Action',
            'model' => 'Model',
            'record_id' => 'Record ID',
            'description' => 'Description',
            'ip_address' => 'IP Address',
            'created_at' => 'Created At',
        ];
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
