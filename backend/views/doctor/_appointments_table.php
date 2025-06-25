<?php
use yii\helpers\Html;
if (empty($appointments)) {
    echo '<div class="text-muted">No appointments found.</div>';
    return;
}
?>
<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Patient Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Duration</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($appointments as $appt): ?>
        <tr>
            <td><?= isset($patientNames[$appt['patient_id']]) ? Html::encode($patientNames[$appt['patient_id']]) : Html::encode($appt['patient_id']) ?></td>
            <td><?= Html::encode($appt['date']) ?></td>
            <td><?= Html::encode($appt['time']) ?></td>
            <td><?= Html::encode($appt['status']) ?></td>
            <td><?= Html::encode($appt['duration']) ?> min</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
