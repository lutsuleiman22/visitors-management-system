<?php

declare(strict_types=1);

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

class ReceptionShift extends ActiveRecord
{
    public const STATUS_OPEN = 1;
    public const STATUS_CLOSED = 0;

    public static function timetableOptions(): array
    {
        return [
            'morning' => 'Morning Shift',
            'afternoon' => 'Afternoon Shift',
            'evening' => 'Evening Shift',
            'night' => 'Night Shift',
            'weekend' => 'Weekend Shift',
        ];
    }

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
        $validTimetables = array_merge(array_keys(self::timetableOptions()), array_values(self::timetableOptions()));

        return [
            [['user_id', 'status'], 'integer'],
            [['branch_code', 'timetable'], 'string', 'max' => 100],
            [['started_at', 'stopped_at'], 'safe'],
            [['user_id', 'branch_code', 'started_at'], 'required'],
            [['status'], 'default', 'value' => self::STATUS_OPEN],
            ['timetable', 'default', 'value' => 'morning'],
            ['timetable', 'in', 'range' => $validTimetables],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}