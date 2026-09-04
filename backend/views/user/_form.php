<?php

declare(strict_types=1);

/** @var yii\bootstrap5\ActiveForm $form */
/** @var common\models\User $model */
use common\models\User;
?>
<?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'email')->input('email', ['maxlength' => true]) ?>
<?= $form->field($model, 'role')->dropDownList(User::roleList()) ?>
<?= $form->field($model, 'status')->dropDownList([User::STATUS_ACTIVE => 'Active', User::STATUS_INACTIVE => 'Inactive', User::STATUS_DELETED => 'Deleted']) ?>
<div class="mb-3"><label class="form-label" for="user-password">Password</label><input type="password" id="user-password" name="password" class="form-control" <?= $model->isNewRecord ? 'required' : '' ?>></div>
