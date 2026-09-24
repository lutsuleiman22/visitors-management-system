<?php

declare(strict_types=1);

/** @var yii\bootstrap5\ActiveForm $form */
/** @var common\models\User $model */
/** @var array<string, string> $branches */
use common\models\User;
?>
<?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'email')->input('email', ['maxlength' => true]) ?>
<?= $form->field($model, 'role')->dropDownList(User::creatableRoleList()) ?>
<?= $form->field($model, 'branch_code')->dropDownList($branches, ['prompt' => 'Select PBZ branch']) ?>
<?= $form->field($model, 'status')->dropDownList([User::STATUS_ACTIVE => 'Active', User::STATUS_INACTIVE => 'Inactive', User::STATUS_DELETED => 'Deleted']) ?>
<?php if ($model->isNewRecord): ?>
    <div class="alert alert-info">
        No password is entered here. When you save the user, the system will send the username,
        a temporary password, and a login link. If the account is set to <strong>Inactive</strong>,
        the user must log in and wait for admin approval before they can create a new password and start their shift.
    </div>
<?php else: ?>
    <div class="mb-3">
        <label class="form-label" for="user-password">New password</label>
        <input type="password" id="user-password" name="password" class="form-control">
        <div class="form-text">Leave blank to keep the current password.</div>
    </div>
<?php endif; ?>
