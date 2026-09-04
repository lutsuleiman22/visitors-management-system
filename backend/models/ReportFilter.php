<?php

declare(strict_types=1);

namespace backend\models;

use common\models\User;
use yii\base\Model;

class ReportFilter extends Model
{
    public string $from_date = '';
    public string $to_date = '';
    public string $role = '';
    public string $status = '';

    public function rules(): array
    {
        return [
            [['from_date', 'to_date', 'role', 'status'], 'safe'],
            [['from_date', 'to_date'], 'date', 'format' => 'php:Y-m-d'],
            [['role'], 'in', 'range' => array_keys(User::roleList())],
            [['status'], 'in', 'range' => ['inside', 'out', 'pending']],
        ];
    }
}
