<?php

declare(strict_types=1);

namespace common\services;

final class BranchCatalog
{
    /** @return array<string, string> */
    public static function all(): array
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

    /** @return array<string, array<string, string>> */
    public static function departments(): array
    {
        return [
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
    }

    /** @return array<string, string> */
    public static function departmentsFor(string $branchCode): array
    {
        return self::departments()[$branchCode] ?? [];
    }
}
