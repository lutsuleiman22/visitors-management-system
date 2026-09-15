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
}
