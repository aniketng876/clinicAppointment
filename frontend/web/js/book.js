// All logic from book.php <script> moved here

const doctorData = window.doctorData;
const daysMap = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Prevent selecting past dates
if (document.getElementById('appointment-date')) {
    document.getElementById('appointment-date').setAttribute('min', new Date().toISOString().split('T')[0]);
}

const doctorSelect = document.getElementById('doctor-select');
if (doctorSelect) {
    document.getElementById('appointment-date').addEventListener('change', function() {
        const doctorId = doctorSelect.value;
        if (!doctorId || !doctorData[doctorId]) return;
        const d = doctorData[doctorId];
        if (!d.working_days) return;
        const workingDays = d.working_days.split(',').map(x => x.trim()).filter(x => x !== '');
        const selectedDate = this.value;
        if (!selectedDate) return;
        const dayShort = new Date(selectedDate).toLocaleDateString('en-US', { weekday: 'short' });
        if (workingDays.indexOf(dayShort) === -1) {
            alert('Selected date is not a working day for this doctor.');
            this.value = '';
        }
    });

    doctorSelect.addEventListener('change', function() {
        const id = this.value;
        if (id && doctorData[id]) {
            const d = doctorData[id];
            let days = 'N/A';
            if (d.working_days && d.working_days.trim() !== '') {
                days = d.working_days.split(',').map(x => x.trim()).filter(x => x !== '').join(', ');
            }
            document.getElementById('working-days').textContent = days;
            document.getElementById('start-time').textContent = d.start_time || 'N/A';
            document.getElementById('end-time').textContent = d.end_time || 'N/A';
            let breakTime = (d.break_time_start && d.break_time_end) ? (d.break_time_start + ' - ' + d.break_time_end) : 'N/A';
            document.getElementById('break-time').textContent = breakTime;
            document.getElementById('doctor-availability').style.display = '';
        } else {
            document.getElementById('doctor-availability').style.display = 'none';
        }
    });
} else {
    // If doctor-select is not present (doctor is booking), always show their own availability
    document.getElementById('doctor-availability').style.display = '';
    const doctorId = Object.keys(doctorData)[0];
    const d = doctorData[doctorId];
    let days = 'N/A';
    if (d.working_days && d.working_days.trim() !== '') {
        days = d.working_days.split(',').map(x => x.trim()).filter(x => x !== '').join(', ');
    }
    document.getElementById('working-days').textContent = days;
    document.getElementById('start-time').textContent = d.start_time || 'N/A';
    document.getElementById('end-time').textContent = d.end_time || 'N/A';
    let breakTime = (d.break_time_start && d.break_time_end) ? (d.break_time_start + ' - ' + d.break_time_end) : 'N/A';
    document.getElementById('break-time').textContent = breakTime;
    // Date change validation for working days
    document.getElementById('appointment-date').addEventListener('change', function() {
        if (!d.working_days) return;
        const workingDays = d.working_days.split(',').map(x => x.trim()).filter(x => x !== '');
        const selectedDate = this.value;
        if (!selectedDate) return;
        const dayShort = new Date(selectedDate).toLocaleDateString('en-US', { weekday: 'short' });
        if (workingDays.indexOf(dayShort) === -1) {
            alert('Selected date is not a working day for you.');
            this.value = '';
        }
    });
}

// Validate selected time is within doctor's working hours and not in break time
const timeInput = document.getElementById('appointment-time');
if (timeInput) {
    timeInput.addEventListener('change', function() {
        let doctorId;
        if (doctorSelect) {
            doctorId = doctorSelect.value;
        } else {
            doctorId = Object.keys(doctorData)[0];
        }
        if (!doctorId || !doctorData[doctorId]) return;
        const d = doctorData[doctorId];
        const selectedTime = this.value;
        if (!selectedTime) return;
        if (d.start_time && d.end_time) {
            if (selectedTime < d.start_time || selectedTime >= d.end_time) {
                alert('Selected time is outside doctor\'s working hours.');
                this.value = '';
                return;
            }
        }
        if (d.break_time_start && d.break_time_end) {
            if (selectedTime >= d.break_time_start && selectedTime < d.break_time_end) {
                alert('Selected time is during doctor\'s break time.');
                this.value = '';
                return;
            }
        }
    });
}

// Calculate and show total fees
const durationInput = document.getElementById('appointment-duration');
const totalFeesSpan = document.getElementById('total-fees');
function updateFees() {
    const duration = parseInt(durationInput.value, 10);
    const fees = duration * 10;
    totalFeesSpan.textContent = '₹' + fees;
}
if (durationInput) {
    durationInput.addEventListener('change', updateFees);
    updateFees();
}

// Prevent manual keyboard input for date and time fields
const apptDate = document.getElementById('appointment-date');
const apptTime = document.getElementById('appointment-time');
if (apptDate) apptDate.addEventListener('keydown', function(e) { e.preventDefault(); });
if (apptTime) apptTime.addEventListener('keydown', function(e) { e.preventDefault(); });
