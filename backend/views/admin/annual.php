<?php

declare(strict_types=1);

use yii\helpers\Url;

?>
<div style="margin-bottom:20px; display:flex; gap:10px;">
	<a href="<?= Url::to(['/admin/reports']) ?>" class="btn btn-light">
		&larr; Back
	</a>
	<a href="<?= Url::to(['/admin/dashboard']) ?>" class="btn btn-warning">
		Dashboard
	</a>
</div>
<?= $this->render('_time-report', ['title' => $title, 'visits' => $visits, 'filterType' => $filterType, 'filterValue' => $filterValue, 'startDate' => $startDate, 'endDate' => $endDate]) ?>
