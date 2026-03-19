# SetupPrinters HRMS

A Human Resource Management System built for **Setu Printers** — covering staff management, attendance tracking, payroll, leave management, and a companion mobile app for QR-based attendance scanning.

## Project Structure

```
setuprinters/
├── backend/        # Laravel 12 web application
└── mobileapp/      # Flutter mobile app (Android/iOS)
```

## Backend (Laravel)

A full-featured HRMS admin panel built with Laravel 12, MySQL, and the Cuba Admin template.

### Features

- **Staff Management** — Create, edit, and organize staff into groups/departments. Configurable wage types (fixed daily or hour-based), overtime settings, and PF deductions.
- **Attendance Tracking** — QR code / barcode-based check-in and check-out via the mobile app. Daily attendance records with automatic status detection (present, half-day, absent, holiday, leave). Nightly cron job marks absent staff.
- **Payroll Report** — Monthly payroll generation from attendance data. Expandable detail rows, editable advance/cash fields with live recalculation, and CSV export.
- **Leave Management** — Staff leave applications with approval/rejection workflow. Supports casual, sick, earned, and unpaid leave types. Bulk approve/reject actions.
- **Holidays** — Financial year-based holiday management (India: April–March). Yearly recurring holidays support. Inline edit and delete with password confirmation.
- **Settings** — Data cleanup for past financial years, password management, and APK download for the mobile app.

### Tech Stack

- **Framework**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL
- **Frontend**: Blade templates, jQuery, Bootstrap 5 (Cuba Admin template)
- **API**: REST endpoints for mobile app integration

### Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Configure DB_CONNECTION, DB_DATABASE, SCAN_API_TOKEN in .env
php artisan migrate
php artisan serve
```

## Mobile App (Flutter)

A companion Android/iOS app for staff attendance — select a user, take a selfie, and scan a QR code to record check-in or check-out.

### Features

- **User Authentication** — Dropdown-based user selection from the staff list fetched via API.
- **Selfie Capture** — Camera capture with automatic center-crop and resize (300x300) before upload.
- **QR Code Scanning** — Scan office QR codes to record `OFFICE IN` (check-in) and `OFFICE OUT` (check-out) with audio beep feedback.
- **Leave Application** — Apply for leave directly from the mobile app.
- **Offline Handling** — Connectivity checks with user-friendly error dialogs.

### Tech Stack

- **Framework**: Flutter (Dart ^3.6.0)
- **HTTP**: Dio with multipart upload support
- **Navigation**: GetX
- **Platforms**: Android, iOS

### Setup

```bash
cd mobileapp
flutter pub get
flutter run
```

### Build

```bash
flutter build apk       # Android
flutter build ios        # iOS
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/user/list` | Fetch staff list for mobile app |
| `POST` | `/api/user/scan-code` | Record attendance scan (multipart: user_id, barcode, selfie) |
| `POST` | `/api/scan` | Legacy scan endpoint with bearer token auth |

## License

Private — Setu Printers internal use only.
