<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model array */
/* @var $isNew boolean */

$this->title = $isNew ? 'Add Doctor' : 'Update Doctor';
?>
<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
<?php if ($isNew): ?>
    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
<?php endif; ?>

<div class="form-group">
    <?= Html::submitButton($isNew ? 'Create' : 'Update', ['class' => 'btn btn-success']) ?>
    <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>
