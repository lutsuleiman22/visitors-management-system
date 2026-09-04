<?php

/** @var yii\web\View $this */
/** @var common\models\Visit[] $visits */

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Visitor Entries';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="visitor-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <?= Html::a('New Check-In', ['check-in'], ['class' => 'btn btn-primary']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => new yii\data\ArrayDataProvider([
            'allModels' => $visits,
            'pagination' => ['pageSize' => 20],
        ]),
        'columns' => [
            [
                'label' => 'Name',
                'value' => static fn (common\models\Visit $visit): string => $visit->visitor->full_name,
            ],
            [
                'label' => 'Origin',
                'value' => static fn (common\models\Visit $visit): string => $visit->from_location ?? '',
            ],
            'host_name:text',
            [
                'attribute' => 'check_in_time',
                'format' => 'datetime',
            ],
            'status:text',
        ],
    ]) ?>
</div>
