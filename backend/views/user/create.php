<?php

declare(strict_types=1);

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Create User';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<h1 class="h3 mb-3"><?= Html::encode($this->title) ?></h1>
<?php $form = ActiveForm::begin(); ?>
<?= $this->render('_form', ['form' => $form, 'model' => $model]) ?>
<?= Html::submitButton('Create User', ['class' => 'btn btn-success']) ?>
<?= Html::a('Cancel', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
<?php ActiveForm::end(); ?>
