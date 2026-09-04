<?php

declare(strict_types=1);

namespace backend\services;

use backend\models\ReportFilter;
use common\models\Visit;
use yii\db\Query;

final class ReportService
{
    public function query(ReportFilter $filter): Query
    {
        $query = (new Query())
            ->from(['v' => Visit::tableName()])
            ->leftJoin(['visitor' => '{{%visitors}}'], 'visitor.id = v.visitor_id')
            ->leftJoin(['host' => '{{%user}}'], 'host.id = v.host_user_id')
            ->select([
                'v.id', 'visitor_name' => 'visitor.full_name', 'visitor_phone' => 'visitor.phone_number',
                'host_name' => 'host.username', 'v.purpose', 'v.from_location', 'v.visitor_pass_number',
                'v.status', 'v.check_in_time', 'v.check_out_time',
            ])
            ->orderBy(['v.check_in_time' => SORT_DESC]);

        if ($filter->from_date !== '') {
            $query->andWhere(['>=', 'v.check_in_time', $filter->from_date . ' 00:00:00']);
        }
        if ($filter->to_date !== '') {
            $query->andWhere(['<=', 'v.check_in_time', $filter->to_date . ' 23:59:59']);
        }
        if ($filter->role !== '') {
            $query->andWhere(['host.role' => $filter->role]);
        }
        if ($filter->status === 'inside') {
            $query->andWhere(['and', ['v.status' => Visit::STATUS_CHECKED_IN], ['v.check_out_time' => null]]);
        } elseif ($filter->status === 'out') {
            $query->andWhere(['or', ['v.status' => Visit::STATUS_CHECKED_OUT], ['not', ['v.check_out_time' => null]]]);
        } elseif ($filter->status === 'pending') {
            $query->andWhere(['v.status' => 'Pending']);
        }

        return $query;
    }

    /** @return array<int, array<string, mixed>> */
    public function rows(ReportFilter $filter): array
    {
        return $this->query($filter)->all();
    }

    public function html(ReportFilter $filter): string
    {
        $rows = $this->rows($filter);
        $html = '<h1>Visitor Management Report</h1><p>Generated: ' . htmlspecialchars(date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8') . '</p><table border="1" cellpadding="5" cellspacing="0" width="100%"><thead><tr><th>Visitor</th><th>Phone</th><th>Host</th><th>Purpose</th><th>Origin</th><th>Pass</th><th>Status</th><th>Check-in</th><th>Check-out</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach (['visitor_name', 'visitor_phone', 'host_name', 'purpose', 'from_location', 'visitor_pass_number', 'status', 'check_in_time', 'check_out_time'] as $field) {
                $html .= '<td>' . htmlspecialchars((string) ($row[$field] ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '</tr>';
        }
        return $html . '</tbody><tfoot><tr><th colspan="9">Total: ' . count($rows) . '</th></tr></tfoot></table>';
    }
}
