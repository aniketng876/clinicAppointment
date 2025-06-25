// This file contains the dashboard calendar and appointment logic

document.addEventListener('DOMContentLoaded', function() {
    var apptDates = window.dashboardAppointmentDates || {};
    var patients = window.dashboardPatientDetails || {};
    var eventDates = Object.keys(apptDates);
    var updateApptStatusUrl = window.updateApptStatusUrl || '';

    // Support all possible VanillaCalendar builds
    var CalendarCtor = null;
    if (window.VanillaCalendar) {
        if (typeof window.VanillaCalendar === 'function') {
            CalendarCtor = window.VanillaCalendar;
        } else if (window.VanillaCalendar.VanillaCalendar) {
            CalendarCtor = window.VanillaCalendar.VanillaCalendar;
        } else if (window.VanillaCalendar.default) {
            CalendarCtor = window.VanillaCalendar.default;
        }
    }
    if (!CalendarCtor) {
        document.getElementById('appointments-list').innerHTML = '<div class="text-danger">Calendar library not loaded or not a constructor. Please check your JS file.</div>';
    } else {
        var calendar = new CalendarCtor('#vanilla-calendar', {
            settings: {
                selection: {
                    day: 'single',
                },
                visibility: {
                    weekend: true,
                },
            },
            actions: {
                clickDay: function(e, self) {
                    var dateStr = self.selectedDates[0];
                    renderAppointmentsTable(dateStr, apptDates, patients);
                }
            },
            popups: {
                // highlight days with appointments
                customDay: function(day, date) {
                    var dateStr = date.toISOString().slice(0, 10);
                    if (eventDates.includes(dateStr)) {
                        day.classList.add('has-appt');
                    }
                }
            }
        });
        calendar.init();
        // Show today's appointments on load
        var today = new Date().toISOString().slice(0, 10);
        // Select today in the calendar and trigger clickDay
        setTimeout(function() {
            var el = document.querySelector('.vanilla-calendar-day[data-calendar-day="' + today + '"]');
            if (el) {
                el.classList.add('vanilla-calendar-day__selected');
                el.click();
            }
        }, 100);
    }

    // Filtering and sorting logic for appointments table
    function getFilterValues() {
        return {
            patient: document.getElementById('appt-filter-patient')?.value || '',
            status: document.getElementById('appt-filter-status')?.value || '',
            date: document.getElementById('appt-filter-date')?.value || '',
            sortBy: document.getElementById('appt-sort-by')?.value || 'date',
            sortDir: document.getElementById('appt-sort-dir')?.dataset.dir || 'asc',
        };
    }

    function setPatientFilterOptions(patients) {
        var sel = document.getElementById('appt-filter-patient');
        if (!sel) return;
        var existing = Array.from(sel.options).map(o => o.value);
        Object.values(patients).forEach(function(p) {
            if (!existing.includes(String(p.id))) {
                var opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.username;
                sel.appendChild(opt);
            }
        });
    }

    function filterAndSortAppointments(appts, filters, patients) {
        let arr = appts.slice();
        // Filtering
        if (filters.patient) arr = arr.filter(a => String(a.patient_id) === filters.patient);
        if (filters.status) arr = arr.filter(a => a.status === filters.status);
        if (filters.date) arr = arr.filter(a => a.date === filters.date);
        // Sorting
        arr.sort(function(a, b) {
            let v1, v2;
            switch (filters.sortBy) {
                case 'date': v1 = a.date; v2 = b.date; break;
                case 'time': v1 = a.time; v2 = b.time; break;
                case 'status': v1 = a.status; v2 = b.status; break;
                case 'patient': v1 = (patients[a.patient_id]?.username || ''); v2 = (patients[b.patient_id]?.username || ''); break;
                default: v1 = a.date; v2 = b.date;
            }
            if (v1 < v2) return filters.sortDir === 'asc' ? -1 : 1;
            if (v1 > v2) return filters.sortDir === 'asc' ? 1 : -1;
            return 0;
        });
        return arr;
    }

    // Patch calendar clickDay to use filtering/sorting
    function renderAppointmentsTable(dateStr, apptDates, patients) {
        var userRole = window.dashboardUserRole || '';
        var appts = apptDates[dateStr] ? apptDates[dateStr].slice() : [];
        var html = '';
        if (appts.length) {
            html += '<h5>Appointments for ' + dateStr + '</h5>';
            html += '<table id="appointments-data-table" class="table table-bordered table-sm"><thead><tr>' +
                '<th>Patient</th><th>Phone</th><th>Time</th><th>Duration</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
            appts.forEach(function(a) {
                var p = patients[a.patient_id];
                html += '<tr>';
                html += '<td>' + (p ? p.username : a.patient_id) + '</td>';
                html += '<td>' + (a.phone || '-') + '</td>';
                html += '<td>' + a.time + '</td>';
                html += '<td>' + (a.duration ? a.duration + ' min' : '-') + '</td>';
                html += '<td>' + a.status + '</td>';
                html += '<td>';
                html += '<button class="btn btn-sm btn-link view-past-appt" data-patient="' + a.patient_id + '" title="View past appointments"><span class="bi bi-clock-history"></span></button>';
                if (a.status !== 'Done') {
                    if (userRole === 'doctor') {
                        html += '<button class="btn btn-link mark-done" data-appt-id="' + a.id + '" style="color:green;margin-left:4px;" title="Mark as Done"><span class="bi bi-check-circle"></span></button>';
                    }
                    html += '<button class="btn btn-link mark-cancelled" data-appt-id="' + a.id + '" style="color:red;margin-left:4px;" title="Mark as Cancelled"><span class="bi bi-x-circle"></span></button>';
                }
                html += '</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
        } else {
            html = '<div class="text-muted">No appointments for this day.</div>';
        }
        document.getElementById('appointments-list').innerHTML = html;
        setTimeout(initAppointmentsDataTable, 100);

        // Move controls node to top of appointments-list (so only one exists)
        var apptList = document.getElementById('appointments-list');
        var controlsNode = apptList.querySelector('#appt-table-controls');
        if (controlsNode) {
            apptList.prepend(controlsNode);
        }

        // Add handler for view-past-appt buttons
        document.querySelectorAll('.view-past-appt').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                ev.stopPropagation();
                var patientId = this.getAttribute('data-patient');
                var past = [];
                Object.keys(apptDates).forEach(function(d) {
                    apptDates[d].forEach(function(a) {
                        if (a.patient_id == patientId && d < dateStr) past.push(a);
                    });
                });
                var p = patients[patientId];
                var html2 = '<div><b>Past Appointments for ' + (p ? p.username : patientId) + ':</b></div>';
                if (past.length) {
                    html2 += '<table class="table table-bordered table-sm"><thead><tr>' +
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

        // Add handler for mark-done buttons
        document.querySelectorAll('.mark-done').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                ev.stopPropagation();
                var apptId = this.getAttribute('data-appt-id');
                var btnEl = this;
                btnEl.disabled = true;
                fetch(updateApptStatusUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': (window.yii && yii.getCsrfToken) ? yii.getCsrfToken() : ''
                    },
                    body: JSON.stringify({ id: apptId, status: 'Done' })
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        // Update status in table and in apptDates
                        var row = btnEl.closest('tr');
                        if (row) {
                            row.querySelector('td:nth-child(5)').textContent = 'Done';
                        }
                        // Also update in apptDates
                        Object.keys(apptDates).forEach(function(d) {
                            apptDates[d].forEach(function(a) {
                                if (a.id == apptId) a.status = 'Done';
                            });
                        });
                        btnEl.disabled = true;
                        btnEl.title = 'Already marked as done';
                    } else {
                        alert('Failed to update status: ' + (data.error || 'Unknown error'));
                        btnEl.disabled = false;
                    }
                })
                .catch(function() {
                    alert('Failed to update status.');
                    btnEl.disabled = false;
                });
            });
        });

        // Add handler for mark-cancelled buttons
        document.querySelectorAll('.mark-cancelled').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                ev.stopPropagation();
                if (!confirm('Are you sure you want to cancel this appointment?')) return;
                var apptId = this.getAttribute('data-appt-id');
                var btnEl = this;
                btnEl.disabled = true;
                fetch(updateApptStatusUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': (window.yii && yii.getCsrfToken) ? yii.getCsrfToken() : ''
                    },
                    body: JSON.stringify({ id: apptId, status: 'Cancelled' })
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        // Update status in table and in apptDates
                        var row = btnEl.closest('tr');
                        if (row) {
                            row.querySelector('td:nth-child(5)').textContent = 'Cancelled';
                        }
                        // Also update in apptDates
                        Object.keys(apptDates).forEach(function(d) {
                            apptDates[d].forEach(function(a) {
                                if (a.id == apptId) a.status = 'Cancelled';
                            });
                        });
                        btnEl.disabled = true;
                        btnEl.title = 'Already cancelled';
                    } else {
                        alert('Failed to cancel appointment: ' + (data.error || 'Unknown error'));
                        btnEl.disabled = false;
                    }
                })
                .catch(function() {
                    alert('Failed to cancel appointment.');
                    btnEl.disabled = false;
                });
            });
        });
    }

    // Show all appointments in a single table on initial load
    function renderAllAppointmentsTable(apptDates, patients) {
        var allAppts = [];
        Object.keys(apptDates).forEach(function(date) {
            apptDates[date].forEach(function(a) {
                allAppts.push(Object.assign({}, a, {date: date}));
            });
        });
        // Sort by date/time ascending
        allAppts.sort(function(a, b) {
            if (a.date !== b.date) return a.date < b.date ? -1 : 1;
            if (a.time !== b.time) return a.time < b.time ? -1 : 1;
            return 0;
        });
        var html = '<div class="d-flex justify-content-between align-items-center mb-2">'
            + '<h5 class="mb-0">All Appointments</h5>'
            + '<button id="show-calendar-view" class="btn btn-outline-primary btn-sm">Calendar View</button>'
            + '</div>';
        html += '<table id="appointments-data-table" class="table table-bordered table-sm"><thead><tr>' +
            '<th>Date</th><th>Patient</th><th>Phone</th><th>Time</th><th>Duration</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        allAppts.forEach(function(a) {
            var p = patients[a.patient_id];
            html += '<tr>';
            html += '<td>' + a.date + '</td>';
            html += '<td>' + (p ? p.username : a.patient_id) + '</td>';
            html += '<td>' + (a.phone || '-') + '</td>';
            html += '<td>' + a.time + '</td>';
            html += '<td>' + (a.duration ? a.duration + ' min' : '-') + '</td>';
            html += '<td>' + a.status + '</td>';
            html += '<td>';
            html += '<button class="btn btn-sm btn-link view-past-appt" data-patient="' + a.patient_id + '" title="View past appointments"><span class="bi bi-clock-history"></span></button>';
            if (a.status !== 'Done') {
                if (window.dashboardUserRole === 'doctor') {
                    html += '<button class="btn btn-link mark-done" data-appt-id="' + a.id + '" style="color:green;margin-left:4px;" title="Mark as Done"><span class="bi bi-check-circle"></span></button>';
                }
                html += '<button class="btn btn-link mark-cancelled" data-appt-id="' + a.id + '" style="color:red;margin-left:4px;" title="Mark as Cancelled"><span class="bi bi-x-circle"></span></button>';
            }
            html += '</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        document.getElementById('appointments-list').innerHTML = html;
        setTimeout(initAppointmentsDataTable, 100);
        // Add handlers for actions
        addAppointmentActionHandlers(apptDates, patients);
        // Calendar view button
        document.getElementById('show-calendar-view').addEventListener('click', function() {
            showCalendarView(apptDates, patients);
        });
    }

    function showCalendarView(apptDates, patients) {
        // Show today's appointments by default
        var today = new Date().toISOString().slice(0, 10);
        renderAppointmentsTable(today, apptDates, patients);
        // Select today in the calendar
        var el = document.querySelector('.vanilla-calendar-day[data-calendar-day="' + today + '"]');
        if (el) {
            el.classList.add('vanilla-calendar-day__selected');
        }
    }

    // Extracted action handlers for reuse
    function addAppointmentActionHandlers(apptDates, patients) {
        // Add handler for view-past-appt buttons
        document.querySelectorAll('.view-past-appt').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                ev.stopPropagation();
                var patientId = this.getAttribute('data-patient');
                var past = [];
                Object.keys(apptDates).forEach(function(d) {
                    apptDates[d].forEach(function(a) {
                        if (a.patient_id == patientId && a.date < (this.closest('tr')?.querySelector('td')?.textContent || '')) past.push(a);
                    });
                });
                var p = patients[patientId];
                var html2 = '<div><b>Past Appointments for ' + (p ? p.username : patientId) + ':</b></div>';
                if (past.length) {
                    html2 += '<table class="table table-bordered table-sm"><thead><tr>' +
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

        // Add handler for mark-done buttons
        document.querySelectorAll('.mark-done').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                ev.stopPropagation();
                var apptId = this.getAttribute('data-appt-id');
                var btnEl = this;
                btnEl.disabled = true;
                fetch(updateApptStatusUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': (window.yii && yii.getCsrfToken) ? yii.getCsrfToken() : ''
                    },
                    body: JSON.stringify({ id: apptId, status: 'Done' })
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        var row = btnEl.closest('tr');
                        if (row) {
                            row.querySelector('td:nth-child(6)').textContent = 'Done';
                        }
                        Object.keys(apptDates).forEach(function(d) {
                            apptDates[d].forEach(function(a) {
                                if (a.id == apptId) a.status = 'Done';
                            });
                        });
                        btnEl.disabled = true;
                        btnEl.title = 'Already marked as done';
                    } else {
                        alert('Failed to update status: ' + (data.error || 'Unknown error'));
                        btnEl.disabled = false;
                    }
                })
                .catch(function() {
                    alert('Failed to update status.');
                    btnEl.disabled = false;
                });
            });
        });

        // Add handler for mark-cancelled buttons
        document.querySelectorAll('.mark-cancelled').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                ev.stopPropagation();
                if (!confirm('Are you sure you want to cancel this appointment?')) return;
                var apptId = this.getAttribute('data-appt-id');
                var btnEl = this;
                btnEl.disabled = true;
                fetch(updateApptStatusUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': (window.yii && yii.getCsrfToken) ? yii.getCsrfToken() : ''
                    },
                    body: JSON.stringify({ id: apptId, status: 'Cancelled' })
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        var row = btnEl.closest('tr');
                        if (row) {
                            row.querySelector('td:nth-child(6)').textContent = 'Cancelled';
                        }
                        Object.keys(apptDates).forEach(function(d) {
                            apptDates[d].forEach(function(a) {
                                if (a.id == apptId) a.status = 'Cancelled';
                            });
                        });
                        btnEl.disabled = true;
                        btnEl.title = 'Already cancelled';
                    } else {
                        alert('Failed to cancel appointment: ' + (data.error || 'Unknown error'));
                        btnEl.disabled = false;
                    }
                })
                .catch(function() {
                    alert('Failed to cancel appointment.');
                    btnEl.disabled = false;
                });
            });
        });
    }

    function initAppointmentsDataTable() {
        var jq = window.jQuery;
        if (jq && jq.fn && jq.fn.DataTable) {
            var t = jq('#appointments-data-table');
            console.log('DataTable init: t.length=', t.length, 'isDataTable:', jq.fn.dataTable.isDataTable('#appointments-data-table'));
            if (t.length) {
                if (jq.fn.dataTable.isDataTable('#appointments-data-table')) {
                    t.DataTable().destroy();
                    console.log('DataTable destroyed');
                }
                t.DataTable({paging:true, searching:true, ordering:true, info:true});
                console.log('DataTable initialized');
            } else {
                console.log('No table found for DataTable');
            }
        } else {
            console.log('jQuery or DataTable not loaded');
        }
    }

    // Hook up controls
    function setupApptTableControls(apptDates, patients) {
        setPatientFilterOptions(patients);
        var controls = ['appt-filter-patient','appt-filter-status','appt-filter-date','appt-sort-by','appt-sort-dir','appt-clear-filters'];
        controls.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', function() {
                    // re-render table for selected day
                    var selected = document.querySelector('.vanilla-calendar-day__selected');
                    var dateStr = selected ? selected.getAttribute('data-calendar-day') : null;
                    if (dateStr) renderAppointmentsTable(dateStr, apptDates, patients);
                });
                if (id === 'appt-sort-dir') {
                    el.addEventListener('click', function() {
                        el.dataset.dir = (el.dataset.dir === 'asc' ? 'desc' : 'asc');
                        var selected = document.querySelector('.vanilla-calendar-day__selected');
                        var dateStr = selected ? selected.getAttribute('data-calendar-day') : null;
                        if (dateStr) renderAppointmentsTable(dateStr, apptDates, patients);
                    });
                }
                if (id === 'appt-clear-filters') {
                    el.addEventListener('click', function() {
                        document.getElementById('appt-filter-patient').value = '';
                        document.getElementById('appt-filter-status').value = '';
                        document.getElementById('appt-filter-date').value = '';
                        document.getElementById('appt-sort-by').value = 'date';
                        document.getElementById('appt-sort-dir').dataset.dir = 'asc';
                        var selected = document.querySelector('.vanilla-calendar-day__selected');
                        var dateStr = selected ? selected.getAttribute('data-calendar-day') : null;
                        if (dateStr) renderAppointmentsTable(dateStr, apptDates, patients);
                    });
                }
            }
        });
    }

    // On first load, show all appointments
    renderAllAppointmentsTable(apptDates, patients);

    setupApptTableControls(apptDates, patients);
});
