<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\Visit $model */
/** @var array<int, string> $visitors */
/** @var array<int, string> $hosts */

use yii\helpers\Html;

$this->title = 'Update Visit #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Visits', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="visit-update">
    <h1 class="h3 mb-3"><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
        'visitors' => $visitors,
        'hosts' => $hosts,
    ]) ?>
</div>
