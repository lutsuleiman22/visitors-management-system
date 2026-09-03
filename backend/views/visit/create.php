<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var common\models\Visit $model */
/** @var array<int, string> $visitors */
/** @var array<int, string> $hosts */

use yii\helpers\Html;

$this->title = 'Create Visit';
$this->params['breadcrumbs'][] = ['label' => 'Visits', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="visit-create">
    <h1 class="h3 mb-3"><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
        'visitors' => $visitors,
        'hosts' => $hosts,
    ]) ?>
</div>
