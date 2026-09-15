<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var frontend\models\ReceptionSignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Create Reception Account';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-lg-5">
                <span class="text-uppercase small fw-semibold text-primary">PBZ reception access</span>
                <h1 class="h3 mt-2">Create reception account</h1>
                <p class="text-body-secondary">Use this account to access visitor check-in and check-out at the selected branch.</p>
                <?php $form = ActiveForm::begin(['id' => 'reception-signup-form']); ?>
                    <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'autocomplete' => 'username']) ?>
                    <?= $form->field($model, 'email')->input('email', ['autocomplete' => 'email']) ?>
                    <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'new-password']) ?>
                    <?= $form->field($model, 'password_repeat')->passwordInput(['autocomplete' => 'new-password']) ?>
                    <div class="d-flex gap-2 flex-wrap mt-3">
                        <?= Html::submitButton('Create Account', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('Back', ['/site/index'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
