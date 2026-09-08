<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $action */
/** @var mixed $userId */
/** @var string $modelName */
/** @var string $dateFrom */
/** @var string $dateTo */

use common\models\AuditLog;
use common\models\User;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Audit Logs';
$this->params['breadcrumbs'][] = $this->title;

$userOptions = ['' => 'All users'] + User::find()->select(['username', 'id'])->indexBy('id')->orderBy(['username' => SORT_ASC])->column();
$actionOptions = [
    '' => 'All actions',
    'LOGIN' => 'LOGIN',
    'CREATE' => 'CREATE',
    'UPDATE' => 'UPDATE',
    'DELETE' => 'DELETE',
    'CHECKOUT' => 'CHECKOUT',
];
?>
<div class="audit-log-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Audit Logs</h1>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <?= Html::beginForm(['audit-log/index'], 'get', ['class' => 'row g-3 align-items-end']) ?>
                <div class="col-md-3">
                    <?= Html::label('User', 'user_id', ['class' => 'form-label']) ?>
                    <?= Html::dropDownList('user_id', $userId, $userOptions, ['class' => 'form-select']) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::label('Action', 'action', ['class' => 'form-label']) ?>
                    <?= Html::dropDownList('action', $action, $actionOptions, ['class' => 'form-select']) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::label('Model', 'model', ['class' => 'form-label']) ?>
                    <?= Html::textInput('model', $modelName, ['class' => 'form-control', 'placeholder' => 'Visitor']) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::label('From', 'date_from', ['class' => 'form-label']) ?>
                    <?= Html::input('date', 'date_from', $dateFrom, ['class' => 'form-control']) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::label('To', 'date_to', ['class' => 'form-label']) ?>
                    <?= Html::input('date', 'date_to', $dateTo, ['class' => 'form-control']) ?>
                </div>
                <div class="col-md-1 d-grid">
                    <?= Html::submitButton('Filter', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover align-middle'],
        'layout' => "{summary}\n{items}\n{pager}",
        'columns' => [
            ['attribute' => 'id', 'headerOptions' => ['style' => 'width:80px;']],
            [
                'attribute' => 'user_id',
                'label' => 'User',
                'value' => static fn (AuditLog $model): string => $model->user ? $model->user->username : 'System',
            ],
            'action',
            'model',
            'description',
            'ip_address',
            [
                'attribute' => 'created_at',
                'label' => 'Created At',
                'format' => ['datetime', 'php:d M Y, H:i:s'],
            ],
        ],
    ]) ?>
</div>
