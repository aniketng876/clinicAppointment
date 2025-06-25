<?php
/**
 * @var $appointment array Appointment details
 * @var $patient array Patient details
 */
?>

<p>Dear <?= htmlspecialchars($patient['username']) ?>,</p>

<p>Your appointment has been successfully booked with the following details:</p>

<ul>
    <li><strong>Date:</strong> <?= htmlspecialchars($appointment['date']) ?></li>
    <li><strong>Time:</strong> <?= htmlspecialchars($appointment['time']) ?></li>
    <li><strong>Doctor:</strong> <?= htmlspecialchars($appointment['doctor_name'] ?? 'Doctor') ?></li>
    <li><strong>Duration:</strong> <?= htmlspecialchars($appointment['duration'] ?? '-') ?> minutes</li>
    <li><strong>Status:</strong> <?= htmlspecialchars($appointment['status']) ?></li>
</ul>

<p>If you have any questions or need to reschedule, please contact us through your dashboard.</p>

<p>Thank you,<br>
Doctor Appointment Team</p>
