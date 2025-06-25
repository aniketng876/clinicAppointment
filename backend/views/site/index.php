<?php

/** @var yii\web\View $this */

$this->title = 'Dashboard';
?>
<div class="site-index">
    <h2>All Users</h2>
    <table class="table table-bordered table-sm">
        <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h2>All Appointments</h2>
    <table class="table table-bordered table-sm">
        <thead><tr><th>ID</th><th>Doctor</th><th>Patient</th><th>Date</th><th>Time</th><th>Status</th><th>Duration</th></tr></thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= isset($doctorMap[$a['doctor_id']]) ? htmlspecialchars($doctorMap[$a['doctor_id']]) : $a['doctor_id'] ?></td>
                <td><?= isset($patientMap[$a['patient_id']]) ? htmlspecialchars($patientMap[$a['patient_id']]) : $a['patient_id'] ?></td>
                <td><?= htmlspecialchars($a['date']) ?></td>
                <td><?= htmlspecialchars($a['time']) ?></td>
                <td><?= htmlspecialchars($a['status']) ?></td>
                <td><?= htmlspecialchars($a['duration']) ?> min</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h2>Doctors & Their Appointment Schedules</h2>
    <ul>
        <?php foreach ($doctorMap as $docId => $docName): ?>
            <li>
                <b><?= htmlspecialchars($docName) ?></b>:
                <a href="<?= \yii\helpers\Url::to(['/doctor/appointments', 'id' => $docId]) ?>" target="_blank">View Calendar</a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
