<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class Branch extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%branches}}';
    }

    public function rules(): array
    {
        return [
            [['code', 'name'], 'required'],
            [['code', 'name'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['name'], 'unique'],
            [['status'], 'integer'],
            [['status'], 'default', 'value' => 1],
        ];
    }

    public function getDepartments(): ActiveQuery
    {
        return $this->hasMany(Department::class, ['branch_id' => 'id'])->orderBy(['name' => SORT_ASC]);
    }
}
