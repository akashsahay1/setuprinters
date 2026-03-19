# SetupPrinters HRMS

A Human Resource Management System built for **Setu Printers** to manage staff attendance, payroll, leaves, and holidays.

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL
- **Frontend**: Blade templates, jQuery, Bootstrap 5 ([Cuba Admin](https://admin.pixelstrap.com/cuba/) template)
- **Mobile Integration**: REST API for scan-based attendance

## Features

### Staff Management
- Create, edit, and manage staff profiles with personal info, bank details, and profile photos
- Organize staff into groups/departments
- Configurable wage calculation (fixed daily or hour-based) with overtime settings
- PF percentage per staff

### Attendance Tracking
- QR code / barcode-based OFFICE IN and OFFICE OUT scanning via mobile app
- Real-time attendance processing on OFFICE OUT scan (`POST /api/scan`)
- Daily attendance records with automatic status detection (present, half-day, absent, holiday, leave)
- Nightly cron job to mark absent staff (`attendance:mark-absent`)

### Payroll Report
- Monthly payroll generation from daily attendance data
- Expandable table rows — key columns visible, details available on click (+/- toggle)
- Editable advance and cash fields with live recalculation
- Save payroll records and export to CSV

### Leave Management
- Staff leave applications with approval/rejection workflow
- Leave types: casual, sick, earned, unpaid
- Bulk approve/reject with SweetAlert2 confirmations
- Auto-updates daily attendance when leaves are approved

### Holidays
- Financial year-based holiday management (India: April-March)
- FY dropdown filter on holidays page
- Inline edit and delete with password confirmation
- Yearly recurring holidays support

### Settings
- Data cleanup: permanently purge all data for a past financial year (holidays, attendance, scans, leaves, payroll)
- Change password
- Download mobile APK

## Project Structure

```
app/
  Http/
    Controllers/
      PageController.php      # Page routes and view data
      AjaxController.php      # All AJAX operations (single /ajax endpoint)
      ScanApiController.php   # Mobile app scan API
    Middleware/
      VerifyScanApiToken.php  # Bearer token auth for scan API
  Models/
    Staff.php, StaffGroup.php, Holiday.php, DailyAttendance.php,
    PayrollRecord.php, LeaveApplication.php, ScannedBarcode.php
  Console/Commands/
    ProcessAttendanceCommand.php   # Bulk attendance processing
    MarkAbsentCommand.php          # Nightly absent marking
resources/views/
  pages/         # All page views (dashboard, staffs, payroll-report, etc.)
  common/        # Shared partials (header, footer, sidebar)
routes/
  web.php        # Web routes
  api.php        # API routes (scan, user list)
  console.php    # Scheduled commands
```

## Setup

```bash
# Clone
git clone https://github.com/akashsahay1/setuprinters.git
cd setuprinters

# Install dependencies
composer install

# Environment
cp .env.example .env
php artisan key:generate

# Configure .env
# DB_CONNECTION=mysql
# DB_DATABASE=setuprintersDB
# SCAN_API_TOKEN=<your-token>

# Run migrations
php artisan migrate

# Serve
php artisan serve
```

## Development Guidelines

1. All AJAX operations go through `AjaxController.php` via `POST /ajax` with `$request->has('action_name')` dispatch
2. Page data is passed from `PageController.php` — do not use API routes in views
3. Use `jQuery` (not `$`) for all JavaScript
4. Use SweetAlert2 for confirmations — never use `confirm()`
5. Follow Cuba Admin template components from `html/template/`
6. Soft delete pattern: `is_deleted` boolean flag (not Laravel SoftDeletes)

## API

### `POST /api/scan`
Mobile app endpoint for recording attendance scans.

**Headers**: `Authorization: Bearer <SCAN_API_TOKEN>`

**Body**:
```json
{
  "user_id": 1,
  "barcode": "OFFICE IN",
  "selfie": ""
}
```

On `OFFICE OUT`, automatically processes the day's attendance for that staff member.

## Scheduled Commands

| Command | Schedule | Description |
|---|---|---|
| `attendance:mark-absent` | Daily 23:55 | Marks staff without scans as absent/holiday/leave |
