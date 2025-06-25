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
            <th>Doctor Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Duration</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($appointments as $appt): ?>
        <tr>
            <td><?= isset($doctorNames[$appt['doctor_id']]) ? Html::encode($doctorNames[$appt['doctor_id']]) : Html::encode($appt['doctor_id']) ?></td>
            <td><?= Html::encode($appt['date']) ?></td>
            <td><?= Html::encode($appt['time']) ?></td>
            <td><?= Html::encode($appt['status']) ?></td>
            <td><?= Html::encode($appt['duration']) ?> min</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
