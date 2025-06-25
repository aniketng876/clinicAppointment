<?php
use yii\helpers\Html;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Appointments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>body{padding:20px;}</style>
</head>
<body>
<h4>Appointments for Dr. <?= Html::encode($doctor['username']) ?></h4>
<ul class="nav nav-tabs" id="apptTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="today-tab" data-bs-toggle="tab" data-bs-target="#today" type="button" role="tab">Today's</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab">Past</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="future-tab" data-bs-toggle="tab" data-bs-target="#future" type="button" role="tab">Future</button>
  </li>
</ul>
<div class="tab-content mt-3">
  <div class="tab-pane fade show active" id="today" role="tabpanel">
    <?= $this->render('_appointments_table', ['appointments' => $todays, 'patientNames' => $patientNames]) ?>
  </div>
  <div class="tab-pane fade" id="past" role="tabpanel">
    <?= $this->render('_appointments_table', ['appointments' => $past, 'patientNames' => $patientNames]) ?>
  </div>
  <div class="tab-pane fade" id="future" role="tabpanel">
    <?= $this->render('_appointments_table', ['appointments' => $future, 'patientNames' => $patientNames]) ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
