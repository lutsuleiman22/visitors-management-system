<?php

declare(strict_types=1);

use yii\helpers\Html;

$this->title = 'Notifications';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1 class="h3 mb-3">Notifications</h1>
<div id="notification-list">
    <?php foreach ($items as $item): ?>
        <div class="alert alert-<?= Html::encode((string) ($item['type'] ?? 'info')) ?> d-flex justify-content-between align-items-center" data-notification-id="<?= (int) ($item['id'] ?? 0) ?>">
            <div>
                <strong><?= Html::encode((string) ($item['title'] ?? 'Notification')) ?></strong>
                <div><?= Html::encode((string) ($item['message'] ?? '')) ?></div>
                <small class="text-body-secondary"><?= Yii::$app->formatter->asDatetime((int) ($item['created_at'] ?? 0)) ?></small>
            </div>
            <?php if (!(int) ($item['is_read'] ?? 0)): ?>
                <button type="button" class="btn btn-sm btn-outline-dark mark-read">Mark read</button>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if ($items === []): ?><p class="text-body-secondary">No notifications.</p><?php endif; ?>
</div>
<?php $this->registerJs(<<<'JS'
(function () {
    document.querySelectorAll('.mark-read').forEach(function (button) {
        button.addEventListener('click', function () {
            const item = button.closest('[data-notification-id]');
            fetch('mark-as-read?id=' + item.dataset.notificationId, { method: 'POST', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content } }).then(function () { button.remove(); item.classList.remove('alert-info', 'alert-warning', 'alert-success', 'alert-danger'); });
        });
    });
    window.setInterval(function () { fetch('feed').then(function (response) { return response.json(); }); }, 15000);
})();
JS); ?>
