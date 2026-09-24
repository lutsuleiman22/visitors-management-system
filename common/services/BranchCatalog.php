<?php

declare(strict_types=1);

namespace common\services;

use common\models\Branch;
use common\models\Department;

final class BranchCatalog
{
    /** @return array<string, string> */
    public static function all(): array
    {
        $known = self::knownBranches();

        try {
            $items = Branch::find()->select(['code', 'name'])->where(['status' => 1])->orderBy(['name' => SORT_ASC])->asArray()->all();
            if ($items === []) {
                return $known;
            }

            $filtered = [];
            foreach ($items as $item) {
                $code = (string) ($item['code'] ?? '');
                if ($code !== '' && array_key_exists($code, $known)) {
                    $filtered[$code] = $known[$code];
                }
            }

            return $filtered === [] ? $known : $filtered;
        } catch (\Throwable) {
            return $known;
        }
    }

    /** @return array<string, array<string, string>> */
    public static function departments(): array
    {
        $fallback = [
            'pbz-head-office' => [
                'customer-service' => 'Customer Service',
                'accounts' => 'Accounts',
                'loans' => 'Loans',
                'human-resources' => 'Human Resources',
                'ict' => 'ICT',
                'management' => 'Branch Management',
            ],
            'pbz-mwanakwerekwe' => [
                'customer-service' => 'Customer Service',
                'accounts' => 'Accounts',
                'loans' => 'Loans',
                'operations' => 'Operations',
            ],
            'pbz-malindi' => [
                'customer-service' => 'Customer Service',
                'accounts' => 'Accounts',
                'loans' => 'Loans',
                'operations' => 'Operations',
            ],
            'pbz-chakechake' => [
                'customer-service' => 'Customer Service',
                'accounts' => 'Accounts',
                'loans' => 'Loans',
            ],
            'pbz-wete' => [
                'customer-service' => 'Customer Service',
                'accounts' => 'Accounts',
                'operations' => 'Operations',
            ],
            'pbz-nungwi' => [
                'customer-service' => 'Customer Service',
                'accounts' => 'Accounts',
                'operations' => 'Operations',
            ],
        ];

        try {
            $items = Department::find()->joinWith('branch')->select(['departments.branch_id', 'departments.code', 'departments.name', 'branches.code AS branch_code'])->asArray()->all();
            if ($items === []) {
                return $fallback;
            }

            $result = [];
            foreach ($items as $item) {
                $branchCode = (string) ($item['branch_code'] ?? '');
                $departmentCode = (string) ($item['code'] ?? '');
                if ($branchCode !== '' && $departmentCode !== '' && array_key_exists($branchCode, self::knownBranches())) {
                    $result[$branchCode][$departmentCode] = $item['name'];
                }
            }

            return $result === [] ? $fallback : $result;
        } catch (\Throwable) {
            return $fallback;
        }
    }

    /** @return array<string, string> */
    private static function knownBranches(): array
    {
        return [
            'pbz-head-office' => 'PBZ Head Office - Zanzibar',
            'pbz-mwanakwerekwe' => 'PBZ Mwanakwerekwe Branch',
            'pbz-malindi' => 'PBZ Malindi Branch',
            'pbz-chakechake' => 'PBZ Chake Chake Branch',
            'pbz-wete' => 'PBZ Wete Branch',
            'pbz-nungwi' => 'PBZ Nungwi Branch',
        ];
    }

    /** @return array<string, string> */
    public static function departmentsFor(string $branchCode): array
    {
        return self::departments()[$branchCode] ?? [];
    }
}
