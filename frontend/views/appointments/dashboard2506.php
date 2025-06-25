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
    <div style="display:flex;align-items:center;gap:10px;">
        <button id="prev-month" class="btn btn-sm btn-outline-secondary" title="Previous Month">&lt;</button>
        <span id="calendar-month-label" style="font-weight:bold;font-size:1.2em;"></span>
        <button id="next-month" class="btn btn-sm btn-outline-secondary" title="Next Month">&gt;</button>
    </div>
    <div id="tui-calendar" style="height: 800px;"></div>
    <div id="calendar"></div>
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

<!-- Add Bootstrap Icons and Bootstrap CSS/JS from local files -->
<link rel="stylesheet" href="/projects/doctorAppointment/frontend/web/css/bootstrap.min.css">
<link rel="stylesheet" href="/projects/doctorAppointment/frontend/web/css/bootstrap-icons.min.css">
<script src="/projects/doctorAppointment/frontend/web/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://uicdn.toast.com/tui-calendar/latest/tui-calendar.css" />

<!-- If you use the default popups, use this. -->
<link rel="stylesheet" type="text/css" href="/projects/doctorAppointment/frontend/web/css/tui-date-picker.css" />
<link rel="stylesheet" type="text/css" href="/projects/doctorAppointment/frontend/web/css/tui-time-picker.css" />

<script src="/projects/doctorAppointment/frontend/web/js/tui-code-snippet.min.js"></script>
<script src="/projects/doctorAppointment/frontend/web/js/tui-time-picker.min.js"></script>
<script src="/projects/doctorAppointment/frontend/web/js/tui-date-picker.min.js"></script>
<script src="/projects/doctorAppointment/frontend/web/js/tui-calendar.js"></script>

<style>
    .calendar-table {
        width: 100%;
        border-collapse: collapse;
    }

    .calendar-table th,
    .calendar-table td {
        border: 1px solid #ccc;
        width: 14%;
        height: 80px;
        text-align: right;
        vertical-align: top;
        padding: 4px;
    }

    .calendar-table th {
        background: #f8f9fa;
    }

    .calendar-table .has-appt a {
        color: #007bff;
        font-weight: bold;
        cursor: pointer;
        text-decoration: underline;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendar = new tui.Calendar('#tui-calendar', {
            defaultView: 'month',
            taskView: false,
            scheduleView: true,
            useDetailPopup: true,
            useCreationPopup: false,
            template: {
                monthDayname: function(dayname) {
                    return '<span class="calendar-day-name">' + dayname.label + '</span>';
                }
            }
        });

        // Prepare schedules from your PHP data
        var apptDates = <?php echo json_encode($appointmentDates); ?>;
        var patients = <?php echo json_encode($patientDetails); ?>;
        var schedules = [];
        Object.keys(apptDates).forEach(function(date) {
            apptDates[date].forEach(function(a) {
                var patient = patients[a.patient_id] ? patients[a.patient_id].username : a.patient_id;
                schedules.push({
                    id: String(a.id),
                    calendarId: '1',
                    title: patient + ' (' + a.time + ')',
                    category: 'time',
                    start: date + 'T' + a.time,
                    end: date + 'T' + a.time, // You can add duration if needed
                    raw: a
                });
            });
        });
        calendar.createSchedules(schedules);

        // Show modal on click
        calendar.on('clickSchedule', function(event) {
            var a = event.schedule.raw;
            var p = patients[a.patient_id];
            var html = '';
            if (p) {
                html += '<div style="margin-bottom:10px;"><b>Patient:</b> ' + p.username + ' (' + p.email + ')</div>';
            }
            html += '<div style="margin-bottom:10px;"><b>Booking Details:</b></div>';
            html += '<table class="table table-bordered"><thead><tr>' +
                '<th>Patient Name</th><th>Phone</th><th>Date</th><th>Time</th><th>Duration</th><th>Status</th><th>Action</th>' +
                '</tr></thead><tbody>';
            html += '<tr>';
            html += '<td>' + (p ? p.username : a.patient_id) + '</td>';
            html += '<td>' + a.phone + '</td>';
            html += '<td>' + a.date + '</td>';
            html += '<td>' + a.time + '</td>';
            html += '<td>' + (a.duration ? a.duration + ' min' : '-') + '</td>';
            html += '<td>' + a.status + '</td>';
            html += '<td>';
            html += '<button class="btn btn-sm btn-link view-past-appt" data-patient="' + a.patient_id + '" title="View past appointments"><span class="bi bi-clock-history"></span></button>';
            html += '<button class="btn btn-link mark-done" data-appt-id="' + a.id + '" style="color:green;margin-left:4px;" title="Mark as Done"><span class="bi bi-check-circle"></span></button>';
            html += '<button class="btn btn-link mark-cancelled" data-appt-id="' + a.id + '" style="color:red;margin-left:4px;" title="Mark as Cancelled"><span class="bi bi-x-circle"></span></button>';
            html += '</td>';
            html += '</tr>';
            html += '</tbody></table>';
            document.getElementById('appointment-modal-body').innerHTML = html;
            var modal = new bootstrap.Modal(document.getElementById('appointmentModal'));
            modal.show();
            // ...add your modal button handlers here as before...
        });
    });

    // --- Calendar with month navigation ---
    function renderCalendar(year, month, appointmentDates) {
        var daysInMonth = new Date(year, month, 0).getDate();
        var firstDayOfWeek = new Date(year, month - 1, 1).getDay();
        var today = new Date();
        var html = '<table class="calendar-table">';
        html += '<tr>';
        ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"].forEach(function(d) {
            html += "<th>" + d + "</th>";
        });
        html += '</tr><tr>';
        for (var i = 0; i < firstDayOfWeek; i++) html += '<td></td>';
        for (var day = 1; day <= daysInMonth; day++) {
            var dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            var hasAppt = appointmentDates[dateStr];
            html += '<td' + (hasAppt ? ' class="has-appt"' : '') + '>';
            if (hasAppt) {
                html += '<a href="#" class="appt-link" data-date="' + dateStr + '">' + day + '</a>';
            } else {
                html += day;
            }
            html += '</td>';
            if (((day + firstDayOfWeek) % 7) == 0) html += '</tr><tr>';
        }
        html += '</tr></table>';
        document.getElementById('calendar').innerHTML = html;
        document.getElementById('calendar-month-label').textContent =
            new Date(year, month - 1).toLocaleString('default', {
                month: 'long',
                year: 'numeric'
            });
    }

    var apptDates = <?php echo json_encode($appointmentDates); ?>;
    var currentYear = new Date().getFullYear();
    var currentMonth = new Date().getMonth() + 1;
    renderCalendar(currentYear, currentMonth, apptDates);

    document.getElementById('prev-month').onclick = function() {
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        renderCalendar(currentYear, currentMonth, apptDates);
    };
    document.getElementById('next-month').onclick = function() {
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        renderCalendar(currentYear, currentMonth, apptDates);
    };

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.appt-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var date = this.getAttribute('data-date');
                var appts = <?php echo json_encode($appointmentDates); ?>;
                var patients = <?php echo json_encode($patientDetails); ?>;
                var html = '';
                if (appts[date]) {
                    // Show patient details for first appointment (assuming all for same patient)
                    var first = appts[date][0];
                    var p = patients[first.patient_id];
                    if (p) {
                        html += '<div style="margin-bottom:10px;"><b>Patient:</b> ' + p.username + ' (' + p.email + ')</div>';
                    }
                    // Booking details for this date in table
                    html += '<div style="margin-bottom:10px;"><b>Booking(s) for ' + date + ':</b></div>';
                    html += '<table class="table table-bordered"><thead><tr>' +
                        '<th>Patient Name</th><th>Phone</th><th>Date</th><th>Time</th><th>Duration</th><th>Status</th><th>Action</th>' +
                        '</tr></thead><tbody>';
                    appts[date].forEach(function(a, idx) {
                        var patientName = patients[a.patient_id] ? patients[a.patient_id].username : a.patient_id;
                        html += '<tr>';
                        html += '<td>' + patientName + '</td>';
                        html += '<td>' + a.phone + '</td>';
                        html += '<td>' + a.date + '</td>';
                        html += '<td>' + a.time + '</td>';
                        html += '<td>' + (a.duration ? a.duration + ' min' : '-') + '</td>';
                        html += '<td>' + a.status + '</td>';
                        html += '<td>';
                        html += '<button class="btn btn-sm btn-link view-past-appt" data-patient="' + a.patient_id + '" title="View past appointments"><span class="bi bi-clock-history"></span></button>';
                        html += '<button class="btn btn-link mark-done" data-appt-id="' + a.id + '" style="color:green;margin-left:4px;" title="Mark as Done"><span class="bi bi-check-circle"></span></button>';
                        html += '<button class="btn btn-link mark-cancelled" data-appt-id="' + a.id + '" style="color:red;margin-left:4px;" title="Mark as Cancelled"><span class="bi bi-x-circle"></span></button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                }
                document.getElementById('appointment-modal-body').innerHTML = html;
                var modal = new bootstrap.Modal(document.getElementById('appointmentModal'));
                modal.show();
                // Add handler for view-past-appt buttons
                document.querySelectorAll('.view-past-appt').forEach(function(btn) {
                    btn.addEventListener('click', function(ev) {
                        ev.stopPropagation();
                        var patientId = this.getAttribute('data-patient');
                        var allAppts = appts;
                        var patient = patients[patientId];
                        var past = [];
                        Object.keys(allAppts).forEach(function(d) {
                            allAppts[d].forEach(function(a) {
                                if (a.patient_id == patientId && d < date) past.push(a);
                            });
                        });
                        var html2 = '<div><b>Past Appointments for ' + (patient ? patient.username : patientId) + ':</b></div>';
                        if (past.length) {
                            html2 += '<table class="table table-bordered"><thead><tr>' +
                                '<th>Date</th><th>Time</th><th>Status</th><th>Duration</th>' +
                                '</tr></thead><tbody>';
                            past.forEach(function(a) {
                                html2 += '<tr>';
                                html2 += '<td>' + a.date + '</td>';
                                html2 += '<td>' + a.time + '</td>';
                                html2 += '<td>' + a.status + '</td>';
                                html2 += '<td>' + (a.duration ? a.duration + ' min' : '-') + '</td>';
                                html2 += '</tr>';
                            });
                            html2 += '</tbody></table>';
                        } else {
                            html2 += '<div>No past appointments found.</div>';
                        }
                        // Open in new pop-up window
                        var win = window.open('', '_blank', 'width=700,height=500,scrollbars=yes');
                        win.document.write('<html><head><title>Past Appointments</title>' +
                            '<link rel="stylesheet" href="/projects/doctorAppointment/frontend/web/css/bootstrap.min.css">' +
                            '<link rel="stylesheet" href="/projects/doctorAppointment/frontend/web/css/bootstrap-icons.min.css">' +
                            '</head><body style="padding:20px;">' + html2 + '</body></html>');
                        win.document.close();
                    });
                });
                // Add handler for mark as done/cancelled buttons
                document.querySelectorAll('.mark-done').forEach(function(btn) {
                    btn.addEventListener('click', function(ev) {
                        ev.stopPropagation();
                        var apptId = this.getAttribute('data-appt-id');
                        if (confirm('Mark this appointment as Done?')) {
                            updateAppointmentStatus(apptId, 'Done');
                        }
                    });
                });
                document.querySelectorAll('.mark-cancelled').forEach(function(btn) {
                    btn.addEventListener('click', function(ev) {
                        ev.stopPropagation();
                        var apptId = this.getAttribute('data-appt-id');
                        if (confirm('Mark this appointment as Cancelled?')) {
                            updateAppointmentStatus(apptId, 'Cancelled');
                        }
                    });
                });
            });
        });
        // Listen for status update from pop-up
        window.addEventListener('message', function(event) {
            if (event.data && event.data.apptId && event.data.status) {
                // Debug: log the payload
                console.log('Sending status update:', event.data);
                fetch(
                        '<?php echo Url::to(['appointments/update-appointment-status']) ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                id: event.data.apptId,
                                status: event.data.status
                            }),
                            credentials: 'same-origin' // Send cookies for CSRF
                        }
                    )
                    .then(resp => resp.json())
                    .then(function(data) {
                        if (data.success) {
                            alert('Status updated!');
                            location.reload();
                        } else {
                            alert('Failed to update status: ' + (data.error || 'Unknown error'));
                        }
                    });
            }
        });

        function updateAppointmentStatus(apptId, status) {
            fetch(
                    '<?php echo Url::to(['appointments/update-appointment-status']) ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            id: apptId,
                            status: status
                        }),
                        credentials: 'same-origin'
                    }
                )
                .then(resp => resp.json())
                .then(function(data) {
                    if (data.success) {
                        alert('Status updated!');
                        location.reload();
                    } else {
                        alert('Failed to update status: ' + (data.error || 'Unknown error'));
                    }
                });
        }
    });
</script>

<!-- Add frontend validation for 10-minute slot multiples in booking/update forms -->
<!-- Example: Ensure duration % 10 === 0 before submitting -->