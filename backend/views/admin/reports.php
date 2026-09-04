<?php

declare(strict_types=1);

use yii\helpers\Html;

$this->title = 'Reports';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1 class="h3 mb-4">Visit Reports</h1>
<div class="row g-3">
    <?php foreach ([['Total Visits', $totalVisits], ['Currently Inside', $checkedIn], ['Checked Out', $checkedOut]] as [$label, $value]): ?>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body"><div class="text-body-secondary small"><?= Html::encode($label) ?></div><div class="display-6 fw-bold"><?= (int) $value ?></div></div></div></div>
    <?php endforeach; ?>
</div>
