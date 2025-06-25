<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

// Collect appointment dates for quick lookup
$appointmentDates = [];
foreach (($appointments ?? []) as $appt) {
    $appointmentDates[$appt['date']][] = $appt;
}

// Get patient details for all appointments
$patientIds = [];
foreach (($appointments ?? []) as $appt) {
    $patientIds[$appt['patient_id']] = true;
}
$patientDetails = [];
if ($patientIds) {
    $ids = implode(',', array_map('intval', array_keys($patientIds)));
    $rows = Yii::$app->db->createCommand("SELECT id, username, email FROM user WHERE id IN ($ids)")->queryAll();
    foreach ($rows as $row) {
        $patientDetails[$row['id']] = $row;
    }
}
?>
<div class="dashboard-calendar">
    <h1><?= Html::encode($this->title) ?></h1>
    <div id="calendar">
        <div id="vanilla-calendar"></div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appointmentModalLabel">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="appointment-modal-body">
            </div>
        </div>
    </div>
</div>

<!-- CSS -->
<link rel="stylesheet" href="/projects/doctorAppointment/frontend/web/css/bootstrap.min.css">
<link rel="stylesheet" href="/projects/doctorAppointment/frontend/web/css/bootstrap-icons.min.css">
<link rel="stylesheet" href="/projects/doctorAppointment/frontend/web/css/vanilla-calendar.min.css">
<link rel="stylesheet" href="/projects/doctorAppointment/frontend/web/css/datatables.min.css">

<!-- JS: jQuery FIRST, then DataTables, then others -->
<script src="/projects/doctorAppointment/frontend/web/js/jquery.min.js"></script>
<script src="/projects/doctorAppointment/frontend/web/js/datatables.min.js"></script>
<script src="/projects/doctorAppointment/frontend/web/js/bootstrap.bundle.min.js"></script>
<script src="/projects/doctorAppointment/frontend/web/js/vanilla-calendar.min.js"></script>

<!-- Debug: Check jQuery and DataTables before dashboard.js -->
<script>
console.log('jQuery version:', window.jQuery && window.jQuery.fn && window.jQuery.fn.jquery);
console.log('DataTable loaded:', !!(window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable));
</script>

<script src="/projects/doctorAppointment/frontend/web/js/dashboard.js"></script>

<div id="appointments-list"></div>

<script>
window.dashboardAppointmentDates = <?php echo json_encode($appointmentDates); ?>;
window.dashboardPatientDetails = <?php echo json_encode($patientDetails); ?>;
window.updateApptStatusUrl = "<?= Url::to(['/appointments/update-appointment-status']) ?>";
window.dashboardUserRole = "<?= Yii::$app->user->identity->role ?>";
</script>

<style>
    .has-appt {
        background-color: #d1e7dd !important;
        border-radius: 50%;
        cursor: pointer;
    }
</style>