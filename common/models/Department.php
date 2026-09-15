<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveRecord;

class Department extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%departments}}';
    }

    public function rules(): array
    {
        return [
            [['branch_id', 'code', 'name'], 'required'],
            [['branch_id'], 'integer'],
            [['code', 'name'], 'string', 'max' => 255],
            [['branch_id', 'code'], 'unique', 'targetAttribute' => ['branch_id', 'code']],
        ];
    }

    public function getBranch(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Branch::class, ['id' => 'branch_id']);
    }
}
