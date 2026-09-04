<?php

declare(strict_types=1);

use common\models\User;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Users</h1><?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?></div>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => ['username', 'email', ['attribute' => 'role', 'value' => static fn (User $model): string => User::roleList()[$model->role] ?? $model->role], ['attribute' => 'status', 'value' => static fn (User $model): string => $model->status === User::STATUS_ACTIVE ? 'Active' : 'Inactive'], ['class' => 'yii\grid\ActionColumn', 'template' => '{update} {delete}', 'buttons' => ['delete' => static fn ($url, User $model): string => Html::a('Deactivate', ['delete', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-danger', 'data' => ['method' => 'post', 'confirm' => 'Deactivate this user?']])]]],
]) ?>
