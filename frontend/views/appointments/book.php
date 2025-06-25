<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Book Appointment';
$this->params['breadcrumbs'][] = $this->title;

$doctorDataJson = json_encode($doctorData);
?>
<div class="appointments-book">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <div class="col-md-6">
            <?php $form = ActiveForm::begin(['id' => 'book-appointment-form']); ?>
            <?php if (isset($patients) && !empty($patients)): ?>
            <div class="form-group">
                <label for="patient-select">Select Patient</label>
                <select id="patient-select" name="patient_id" class="form-control" required>
                    <option value="">-- Select Patient --</option>
                    <?php foreach ($patients as $pat): ?>
                        <option value="<?= $pat['id'] ?>"><?= Html::encode($pat['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <?php if (!isset($patients) || empty($patients)): ?>
            <div class="form-group">
                <label for="doctor-select">Select Doctor</label>
                <select id="doctor-select" name="doctor_id" class="form-control">
                    <option value="">-- Select Doctor --</option>
                    <?php foreach ($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>"><?= Html::encode($doc['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div id="doctor-availability" style="display:none; margin-top:20px;">
                <h4>Doctor Availability</h4>
                <div><b>Working Days:</b> <span id="working-days"></span></div>
                <div><b>Start Time:</b> <span id="start-time"></span></div>
                <div><b>End Time:</b> <span id="end-time"></span></div>
                <div><b>Break Time:</b> <span id="break-time"></span></div>
                <div style="margin-top:20px;">
                    <label for="appointment-date">Select Date</label>
                    <input type="date" id="appointment-date" name="date" class="form-control" required>
                </div>
                <div style="margin-top:10px;">
                    <label for="appointment-time">Select Time</label>
                    <input type="time" id="appointment-time" name="time" class="form-control" required>
                </div>
                <div style="margin-top:10px;">
                    <label for="appointment-phone">Phone Number</label>
                    <input type="text" id="appointment-phone" name="phone" class="form-control" required maxlength="32">
                </div>
                <div style="margin-top:10px;">
                    <label for="appointment-duration">Duration</label>
                    <select id="appointment-duration" name="duration" class="form-control" required>
                        <option value="10">10 minutes</option>
                        <option value="20">20 minutes</option>
                        <option value="30">30 minutes</option>
                    </select>
                </div>
                <div style="margin-top:10px;">
                    <label>Total Fees:</label>
                    <span id="total-fees" style="font-weight:bold;">₹100</span>
                </div>
                <div style="margin-top:20px;">
                    <button type="submit" class="btn btn-primary">Book Appointment</button>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<script>
window.doctorData = <?= $doctorDataJson ?>;
</script>
<script src="/projects/doctorAppointment/frontend/web/js/book.js"></script>