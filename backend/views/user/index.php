<?php

declare(strict_types=1);

use common\models\User;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var array<string, string> $branches */
/** @var common\models\User[] $pendingUsers */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Users</h1><div class="d-flex gap-2"><?= Html::a('Pending Approvals (' . count($pendingUsers) . ')', ['index', 'branch' => $selectedBranch, '#' => 'pending-approvals'], ['class' => 'btn btn-warning']) ?><?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?></div></div>
<div id="pending-approvals" class="card border-warning mb-4">
    <div class="card-header bg-warning-subtle"><strong>Reception approvals</strong><span class="ms-2 badge text-bg-warning"><?= count($pendingUsers) ?> pending</span></div>
    <div class="card-body p-0">
        <?php if ($pendingUsers === []): ?>
            <p class="text-body-secondary m-3 mb-0">No pending reception accounts.</p>
        <?php else: ?>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Username</th><th>Email</th><th>PBZ Branch</th><th>Created</th><th></th></tr></thead><tbody>
            <?php foreach ($pendingUsers as $pendingUser): ?>
                <tr><td><?= Html::encode($pendingUser->username) ?></td><td><?= Html::encode($pendingUser->email) ?></td><td><?= Html::encode($branches[$pendingUser->branch_code] ?? 'Unassigned') ?></td><td><?= Html::encode(date('Y-m-d H:i', (int) $pendingUser->created_at)) ?></td><td><?= Html::beginForm(['approve', 'id' => $pendingUser->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('Approve', ['class' => 'btn btn-sm btn-success', 'data-confirm' => 'Approve this reception account?']) ?><?= Html::endForm() ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>
</div>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => ['username', 'email', ['attribute' => 'role', 'value' => static fn (User $model): string => User::roleList()[$model->role] ?? $model->role], ['attribute' => 'branch_code', 'label' => 'PBZ Branch', 'value' => static fn (User $model): string => $branches[$model->branch_code] ?? 'Unassigned'], ['attribute' => 'status', 'value' => static fn (User $model): string => $model->status === User::STATUS_ACTIVE ? 'Active' : 'Inactive'], ['class' => 'yii\grid\ActionColumn', 'template' => '{update} {delete}', 'buttons' => ['delete' => static fn ($url, User $model): string => Html::a('Deactivate', ['delete', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-danger', 'data' => ['method' => 'post', 'confirm' => 'Deactivate this user?']])]]],
]) ?>
