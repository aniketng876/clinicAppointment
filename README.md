<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Doctor Appointment System (Yii2 Advanced)</h1>
    <br>
</p>

A robust doctor appointment system built on the Yii2 Advanced Project Template.

## Project Setup

1. **Clone the repository**
   ```
   git clone <repo-url>
   cd doctorAppointment
   ```
2. **Install dependencies**
   ```
   composer install
   ```
3. **Configure your database**
   - Edit `common/config/main-local.php` and `backend/config/main-local.php`/`frontend/config/main-local.php` with your DB credentials.
4. **Run migrations**
   ```
   php yii migrate
   ```
5. **Set up web servers**
   - Point your web server's document root to `frontend/web` for the patient/doctor panel and `backend/web` for the admin panel.
6. **Create an admin user**
   - Register via frontend or insert via SQL (see below).
7. **Login and use the system!**

## Features

- **User Authentication & Roles**: Admin, Doctor, Patient roles with secure login and role-based access.
- **Admin Panel**:
  - Manage doctors and patients (add, update, delete, view appointments).
  - View all doctors and patients in tabular format (no IDs shown).
  - For each doctor/patient, view all appointments in a pop-up with tabs for Today's, Past, and Future appointments.
- **Doctor Panel**:
  - Dashboard with calendar view of appointments (clickable dates, modal with appointment details).
  - Tabbed settings page to manage working days, working hours, break times, and holidays.
  - Can view and update appointment status (AJAX, modal pop-up).
- **Patient Panel**:
  - Book appointment form with doctor selection, live working days/times display, and fee calculation.
  - JS validation for date, time, duration, and phone.
  - Prevents double-booking, booking in break time, or on non-working days.
- **Appointments**:
  - Stores doctor, patient, date, time, duration, phone, and status.
  - Status can be updated by doctor/admin (AJAX, modal pop-up).
  - Appointments are shown in calendar/dashboard for doctors and in pop-ups for admin.
- **Database**:
  - User table with role column.
  - Doctor settings and holidays tables.
  - Appointments table with all relevant fields.
- **UI**:
  - Bootstrap 5 styling and icons.
  - Responsive, modern, and user-friendly.

## Directory Structure

```
common/         Shared models, config, mail, tests
console/        Console commands, migrations
backend/        Admin panel (controllers, views, models)
frontend/       Doctor & patient panel (controllers, views, models)
vendor/         Composer dependencies
environments/   Environment configs
```

## Admin User Creation (SQL Example)

```
INSERT INTO user (username, email, password_hash, auth_key, role, status, created_at, updated_at)
VALUES ('admin', 'admin@example.com', '<hash>', '<auth_key>', 'admin', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
```
Generate `<hash>` using Yii2: `Yii::$app->security->generatePasswordHash('admin')`

## License

MIT
