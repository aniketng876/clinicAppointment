<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Doctor Settings';
$this->params['breadcrumbs'][] = $this->title;

$days = [
    'Mon' => 'Monday',
    'Tue' => 'Tuesday',
    'Wed' => 'Wednesday',
    'Thu' => 'Thursday',
    'Fri' => 'Friday',
    'Sat' => 'Saturday',
    'Sun' => 'Sunday',
];

// Load working days from JSON if available
$selected = [];
if ($model->working_days) {
    if (is_array($model->working_days)) {
        $selected = $model->working_days;
    } else {
        $selected = explode(',', $model->working_days);
    }
}
?>
<div class="doctor-settings">
    <h1><?= Html::encode($this->title) ?></h1>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link<?= $tab === 'working-days' ? ' active' : '' ?>" href="<?= \yii\helpers\Url::to(['settings', 'tab' => 'working-days']) ?>">Working Days</a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $tab === 'holidays' ? ' active' : '' ?>" href="<?= \yii\helpers\Url::to(['settings', 'tab' => 'holidays']) ?>">Holidays</a>
        </li>
    </ul>
    <?php if ($tab === 'working-days'): ?>
        <!-- Working Days Form -->
        <?php $form = ActiveForm::begin(); ?>
        <div class="mb-3">
            <label>Working/Non-working Days:</label>
            <div class="d-flex flex-wrap">
                <?php foreach ($days as $key => $label): ?>
                    <div class="form-check me-4 mb-2">
                        <input class="form-check-input" type="checkbox" id="day-<?= $key ?>" name="working_days[]" value="<?= $key ?>" <?= in_array($key, $selected) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="day-<?= $key ?>"> <?= $label ?> </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <?= $form->field($model, 'start_time')->input('time') ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'end_time')->input('time') ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'break_time_start')->input('time') ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'break_time_end')->input('time') ?>
            </div>
        </div>
        <input type="hidden" id="doctorsettingsform-working_days" name="DoctorSettingsForm[working_days]" value="<?= Html::encode(implode(',', $selected)) ?>">
        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    <?php elseif ($tab === 'holidays'): ?>
        <div class="mb-4">
            <?php
            /** @var \frontend\models\DoctorHolidayForm $holidayModel */
            $holidayModel = $holidayModel ?? new \frontend\models\DoctorHolidayForm();
            ?>
            <?php $form = ActiveForm::begin(['action' => ['settings', 'tab' => 'holidays']]); ?>
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($holidayModel, 'date')->input('date') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($holidayModel, 'description')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <?= Html::submitButton('Add Holiday', ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <h5>Existing Holidays</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($holidays as $holiday): ?>
                <tr>
                    <td><?= Html::encode($holiday['date']) ?></td>
                    <td><?= Html::encode($holiday['description']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script>
// On change, update hidden input with comma-separated checked days
const checkboxes = document.querySelectorAll('input[name="working_days[]"]');
const hiddenInput = document.getElementById('doctorsettingsform-working_days');
function updateWorkingDays() {
    const checked = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
    hiddenInput.value = checked.join(',');
}
checkboxes.forEach(cb => cb.addEventListener('change', updateWorkingDays));
updateWorkingDays(); // initialize on page load

// On submit, ensure value is up to date
const form = document.querySelector('form');
form.addEventListener('submit', function(e) {
    updateWorkingDays();
});
</script>
