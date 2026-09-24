<?php

declare(strict_types=1);

use common\models\ShiftTimetable;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Timetables';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="text-uppercase small fw-semibold text-primary">Operations control</div>
        <h1 class="h2 mb-1">Timetables</h1>
        <p class="text-body-secondary mb-0">Create and manage shift timetable templates for reception teams.</p>
    </div>
    <?= Html::a('Back to Dashboard', ['/admin/dashboard'], ['class' => 'btn btn-warning']) ?>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Create timetable</h2>
                <?php $form = ActiveForm::begin(); ?>
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Morning Shift']) ?>
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'morning']) ?>
                    <?= $form->field($model, 'start_time')->textInput(['type' => 'time']) ?>
                    <?= $form->field($model, 'end_time')->textInput(['type' => 'time']) ?>
                    <?= $form->field($model, 'status')->dropDownList([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]) ?>
                    <div class="d-grid">
                        <?= Html::submitButton('Save Timetable', ['class' => 'btn btn-primary']) ?>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($timetables === []): ?>
                            <tr>
                                <td colspan="5" class="text-center text-body-secondary py-5">No timetable entries yet.</td>
                            </tr>
                        <?php else: foreach ($timetables as $timetable): ?>
                            <tr>
                                <td><?= Html::encode($timetable->name) ?></td>
                                <td><?= Html::encode($timetable->code) ?></td>
                                <td><?= Html::encode((string) $timetable->start_time) ?></td>
                                <td><?= Html::encode((string) $timetable->end_time) ?></td>
                                <td>
                                    <span class="badge text-bg-<?= (int) $timetable->status === 1 ? 'success' : 'secondary' ?>">
                                        <?= (int) $timetable->status === 1 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
