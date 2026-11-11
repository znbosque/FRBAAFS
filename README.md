# DRLCEFI Attendance and Feedback System

DRLCEFI is a Laravel 12, Blade, vanilla JavaScript, and SQLite student attendance application. Its dashboard opens directly to the login screen. Student face photos are stored on a private filesystem disk; passwords are hashed and the “Remember password” checkbox uses Laravel's persistent-session cookie, never browser local storage.

## Application modules

| Module | Behavior |
| --- | --- |
| Login and accounts | Signup (Administrator role), verified email, persistent login, logout, login lockout after three bad attempts, profile/photo editing, password change, and six-digit email password reset |
| Home | Searchable student directory, alphabetically ordered and paginated by 10, with student profile and administrator row actions |
| Students | Student ID, names, course, year, school year, parent phone, regular/irregular status, private pose photos, profile, edit, archive and delete |
| Attendance | Search and filters by date, subject, status, and course; date navigation is capped at today; archive selected date/school year |
| Schedules | Administrator CRUD for school year, course, subject, instructor, room, weekday, and class start/end |
| Reports | Daily sheet and date-range per-student Present/Late/Absent totals and percentage; weekday/course filters; browser print / Save as PDF |
| Overview | Daily, weekly and monthly attendance summaries |
| Notification History | Recorded notification attempts, attendance grouped by date, recent audit activity, and administrator preferences; no browser SMS sending |
| Archives | School-year groups, student/attendance/schedule views, school-year restore, and administrator archiving |
| Operation Mode | Separate, movable camera scanner backed by the local Python/OpenCV stream service; matched faces create one General check-in per day. |

## Setup

Requirements: PHP 8.2+, Composer, Node.js/npm, SQLite (or MySQL), and Python 3 for the optional camera service.

```powershell
cd frbaafs
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
npm run build
php artisan storage:link
php artisan migrate --seed
php artisan serve
```

Open <http://localhost:8000>. Signup and email verification are available. On a completely empty database, the seeder creates a small example dataset and `admin@example.com`; it prints a randomly generated admin password once. Set `SEED_ADMIN_PASSWORD` in `.env` before first seeding if a chosen password is required. The seeder does not add example rows to a non-empty database.

For development, `MAIL_MAILER=log` writes verification links to the Laravel log. Password reset codes deliberately require SMTP; configure `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, and `MAIL_FROM_ADDRESS`. Codes are hashed in the database, six digits, one-time, and expire after 15 minutes.

Email verification links are signed and can be opened on a different device from the one used to register. Verification does not sign in that device; return to the original device and log in there. For local phone testing, start the app with `php artisan serve --host=0.0.0.0`, set `APP_URL` to the computer's LAN address (for example, `http://192.168.1.20:8000`), and open the app on both devices using that address while connected to the same network. A `localhost` link points back to the phone itself. For real deployments, use a public HTTPS `APP_URL`.

## Attendance boundaries

The browser face-recognition endpoint is a **recognition preview only**. Desktop Operation Mode uses the authenticated Laravel session to record one `General` check-in per active student per day after a face match; repeat scans do not check the student out. The dashboard does not send SMS. Attendance writes from external device clients require a bearer token configured as `ATTENDANCE_DEVICE_TOKEN` in `.env`:

```http
GET  /api/device/students
POST /api/device/attendance
Authorization: Bearer <ATTENDANCE_DEVICE_TOKEN>
```

`GET /api/device/students` returns active students' photo data for a trusted device client. A `POST /api/device/attendance` JSON body contains `student_id` and `subject`. Its first scan is time-in; the next scan for that student/subject/date is time-out; another time-out is rejected. Status is Late only after a matching, non-archived schedule's start time; without a matching schedule it is Present. Run Laravel's scheduler (`php artisan schedule:work`) for ended class schedules to create Absent records for students without an attendance record.

## Raspberry Pi SIM7600G SMS relay

The PC Operation Mode scanner queues parent check-in messages in Laravel. A Raspberry Pi on the same network polls Laravel for messages and sends them through its attached SIM7600G; it does not need an inbound port or a Twilio account. Check-in and check-out notifications can be enabled separately under Notification History. Operation Mode itself only checks students in; check-out notifications are queued when a check-out is recorded through the device attendance API.

Run `php artisan migrate` on the Laravel computer to create the SMS outbox fields before starting the relay.

Install the SMS relay dependencies in the Pi's Python environment, connect the SIM7600G modem's AT serial port, and run this relay (use the actual Pi serial port exposed by the modem):

```bash
python3 -m pip install -r tools/requirements-sms.txt
python3 tools/raspberry_pi_sms_relay.py \
  --server http://<laravel-computer-lan-ip>:8000 \
  --token "$ATTENDANCE_DEVICE_TOKEN" \
  --modem /dev/ttyUSB2
```

The modem port and baud rate depend on the SIM7600G board and operating system; the relay defaults to 115200 baud and accepts `--baud` to override it. The Pi needs network access to Laravel, a working SIM with SMS service, and an available modem AT port. Keep the token private. SMS status is listed in Notification History; a provider/modem acceptance response is recorded as sent, but final handset delivery depends on the mobile network. The existing `tools/raspberry_pi_client.py --modem ...` option is for the separate Pi face-scanner workflow; use `raspberry_pi_sms_relay.py` when the PC Operation Mode camera should trigger the Pi modem.

## Optional desktop OpenCV service

Install OpenCV and NumPy in a Python environment on the computer with the webcam:

```powershell
python -m pip install opencv-python numpy
python tools/operation_camera.py --origin http://localhost:8000
```

If the Laravel page is opened at `http://127.0.0.1:8000`, use that exact origin in `--origin`. Keep the camera service bound to localhost. Open **Operation Mode** in Laravel and launch the separate scanner window. On Windows, camera detection tries DirectShow, Media Foundation, and OpenCV's default backend. If the webcam does not appear, close other apps using it and check Windows Settings > Privacy & security > Camera. Face detection and matching use the bundled browser models and enrolled student descriptors; Python's optional `face-recognition` package is not required. The Python service only streams video; the authenticated Laravel page submits a matched descriptor to record attendance. The scanner records `General` attendance for today with status Present and does not automatically record time-out.

## Optional Raspberry Pi attendance client

The sample client syncs active student photos using the protected device endpoint, recognizes faces locally, and posts a scan only when an operator presses `C`:

```bash
python -m pip install -r tools/requirements-camera.txt
python tools/raspberry_pi_client.py \
  --server http://<laravel-host>:8000 \
  --token "$ATTENDANCE_DEVICE_TOKEN" \
  --subject "Networking"
```

An optional serial SMS modem can be enabled with `--modem /dev/ttyUSB0`; install `pyserial` separately. The client sends an SMS only after a device check-in response. Do not expose the device token in public source control or logs.

## Security and storage

- Student face photos are saved under `storage/app/private/student-faces` and served through an authenticated Laravel route. The device photo-sync endpoint requires the same bearer token as attendance writes.
- Passwords and reset codes are hashed. Theme preference is the only persistent browser setting; passwords are never saved in local storage.
- Audit events include profile, student, schedule, archive, notification-setting, and attendance changes.
- Keep `.env`, SMTP credentials, and the device token private. Use HTTPS for deployed installations.

## Tests

```powershell
php artisan test
npm run build
```

The feature tests cover account flows and lockout, password reset codes, student/photo enrollment, preview-only recognition, schedule-based status, protected device attendance, reports, and archives.
