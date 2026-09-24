<?php

declare(strict_types=1);

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class ShiftTimetable extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%shift_timetables}}';
    }

    public function behaviors(): array
    {
        return [TimestampBehavior::class];
    }

    public function rules(): array
    {
        return [
            [['name', 'code'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['code'], 'string', 'max' => 50],
            [['start_time', 'end_time'], 'string', 'max' => 20],
            [['start_time', 'end_time'], 'default', 'value' => '08:00:00'],
            [['status'], 'integer'],
            [['status'], 'default', 'value' => 1],
            [['name', 'code'], 'trim'],
            [['code'], 'match', 'pattern' => '/^[a-z0-9\-_]+$/i'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Timetable Name',
            'code' => 'Code',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'status' => 'Status',
        ];
    }
}
