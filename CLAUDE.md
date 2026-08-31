# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Laravel 10 HR/payroll/attendance management system built on the Stisla Bootstrap admin
theme (assets only — this is not the vendor's demo app). It covers employee management,
attendance/roster/fingerprint recap, leave & TOIL (time-off-in-lieu), payroll, SK letters
(employment decree documents), asset management, and a mobile-facing API used by a companion
attendance app (face-recognition check-in via an external DeepFace service).

## Commands

```bash
# PHP dependencies / app
composer install
php artisan serve

# Front-end assets (Laravel Mix, not Vite)
npm install
npm run dev          # dev build (alias: npm run development)
npm run watch
npm run watch-poll    # for filesystems where inotify doesn't work (e.g. some VM/Docker setups)
npm run prod          # production build (alias: npm run production)

# Tests (PHPUnit — no Pest)
php artisan test
php artisan test --filter=TestName
vendor/bin/phpunit tests/Feature/SomeTest.php

# Queue / scheduler (payroll emails, WhatsApp/Telegram notifications, leave accrual all run as jobs/commands)
php artisan queue:work
php artisan schedule:work

# Common Laravel housekeeping
php artisan migrate
php artisan permission:cache-reset   # after changing roles/permissions, since Spatie permissions are cached
```

Note: `tests/` currently only contains the framework's default example tests — there is no
real automated test suite to run as a regression check yet.

## Architecture

### Multi-database setup
`config/database.php` defines three MySQL connections: `mysql` (default/primary app data),
`mysql_second`, and `mysql_third` (env vars `DB_SECOND_*` / `DB_THIRD_*`). These are used for
cross-database reads (e.g. pulling raw fingerprint/device data from an external system) —
check a model/query's connection before assuming it hits the primary database.

### Authorization: Spatie permissions, not custom gates
All access control goes through `spatie/laravel-permission`. `AuthServiceProvider` explicitly
does *not* define custom `Gate::define()` calls — permissions/roles are the only mechanism.
Route protection is applied per-group in `routes/web.php` (a single ~1000-line file) using
`permission:XxxYyy` and `role:RoleName` middleware, often with `|`-separated alternatives (e.g.
`permission:ManageFingerspotSPVManager|ManageFingerspot|ViewFingerspot`) to grant tiered access
(full manage vs. SPV/manager-scoped vs. read-only) to the same feature area. When adding a new
protected route, follow the existing naming convention for permissions (`Manage<Feature>`,
`View<Feature>`, `<Feature>SPVManager` for supervisor/manager-scoped variants) and register the
permission via the `permission.php` config / seeders rather than inventing ad hoc gate checks.
After changing roles/permissions in code or via tinker, permissions are cached — reset with
`app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions()` or
`php artisan permission:cache-reset`.

### Role-based dashboards
There is no single dashboard route — `getDashboardRoute()` in `app/helpers.php` (autoloaded
globally via composer's `files` autoload) inspects `Auth::user()->hasRole(...)` and returns a
different view name per role (Admin, HeadHR|HR, Human, Manager, Director, Supervisor, employee,
Training), each backed by its own controller (`DashboardController`, `DashboardHRController`,
`DashboardHumanController`, `DashboardManagerController`, `DashboardDirController`,
`DashboardSupervisorController`, `dashboardAdminController`, etc.) and its own view under
`resources/views/pages`. `DashboardRedirectService` centralizes the redirect-after-login logic
that uses this.

### Web app vs. mobile API
`routes/web.php` is the traditional session/Blade-based admin app (Stisla UI, one controller
per feature, heavy use of `permission:` middleware groups). `routes/api.php` is a separate,
much smaller surface for the companion mobile attendance app: token auth via
`auth:sanctum` + a custom `device.check` (`CheckDeviceBinding`) middleware that ties a
Sanctum token to a single registered device, plus `SingleDeviceLogin` in the web middleware
group enforcing one active session per user account. Mobile login is rate-limited
(`throttle:10,1`), and mobile check-in/check-out are additionally throttled tighter than
read-only endpoints because they call an external DeepFace face-recognition service
(`DEEPFACE_SERVICE_URL` in `.env`) for liveness/identity verification.

### Feature areas and their controllers/services
- **Attendance & fingerprint recap**: `FingerspotController`/`FingerprintsController` pull raw
  punches from fingerprint devices (cross-DB), `FingerprintRecapCalculator` derives daily
  attendance summaries, `AttendancetotalController`/`Attendancetotal` model aggregate totals,
  `ManualRecapController` handles manual corrections, `Editedfingerprints` /
  `EditedFingerprintAttachment` track manual edits with audit attachments.
- **Roster/scheduling**: `RosterController`, `AutoRosterController` /
  `AutoRosterOtherStoreController` (auto-generation, including cross-store assignment),
  `RosterforSupervisorManagerController`, `ShiftsController`, `Schedule` model.
- **Leave & TOIL**: `LeaveController`/`LeaverequestController`/`LeavetypesController`/
  `LeavebalancesController` for standard leave; `ToilController`/`ToilLeaveRequestsController`
  for time-off-in-lieu, each with its own balance model (`Leavebalance` vs `Toilbalances`).
  `GiveAnnualLeave`, `GenerateSpecialLeave`, `generateleave` console commands drive scheduled
  accrual.
- **Payroll**: `PayrollController`/`PayrollsController`/`PayrollPeriodController`/
  `PayrollcomponentsController`/`SalaryController`, backed by `PayrollService` (calculation) and
  `PayrollSlipService` (slip generation/PDF via `barryvdh/laravel-dompdf` or
  `barryvdh/laravel-snappy`), `PayrollEmailController` + `SendPayrollEmails` command +
  `PAYROLL_MAIL_*` env vars use a **separate SMTP identity** from the app's default mailer —
  don't conflate the two when debugging email issues. `SendPayrollSlipJob` and
  `GeneratePayrollIntroLetterJob` handle the async generation/delivery.
- **SK letters** (employment decree/SK documents — promotions, contracts, etc.):
  `SkLetterController`/`SktemplateController`/`SkemployeeController`, generated via
  `SkLetterService` and `DocumentGeneratorService` /
  `DocumentPengantarKaryawanGeneratorService`.
- **Employee lifecycle**: `EmployeeController` plus split-out concerns
  (`EmployeePositionandAtasanController` for position/superior assignment,
  `EmployeeSalaryController`, `EmployeeTrainingController`, `ContractController`,
  `PositionreqController`/`PositionapprovalController` for position change request/approval
  workflows), `EmployeeImportController` for bulk import (via `maatwebsite/excel` /
  `phpoffice/phpspreadsheet` — see `app/Imports`, `app/Exports`).
- **Notifications**: `TelegramNotifier` service, `SendWhatsappJob`/`SendWhatsappReminder3Month`
  jobs, `SendAnnouncementEmail`/`SendProbationReminderEmail` — this app pushes notifications
  through multiple channels (email, WhatsApp, Telegram) depending on feature, not just Laravel's
  default mail.

### Console commands (scheduled jobs)
`app/Console/Commands` holds the scheduled maintenance/business-logic jobs: leave accrual
(`GiveAnnualLeave`, `GenerateSpecialLeave`, `generateleave`), payroll cleanup
(`DeleteOldPayrolls`), reminders (`SendAttendanceReminder`, `SendProbationReminder`),
session cleanup (`CleanupUserSessions`), and one-off/periodic sync commands that reconcile
denormalized "primary" fields on `Employee` from related tables
(`SyncPrimaryDepartmentToEmployee`, `SyncPrimaryPositionToEmployee`,
`SyncPrimaryStoreToEmployee`, `SyncEmployeePositions`) — note `Employee` stores denormalized
current department/position/store alongside the normalized `EmployeeDepartment` /
`EmployeePosition` / `EmployeeStore` history tables, so changes to assignment history must keep
both in sync (these commands exist precisely to repair drift between them).

### Security middleware of note
- `SingleDeviceLogin` (web) and `CheckDeviceBinding` (API) together enforce single-session/
  single-device login for regular users.
- `RequireTwoFactor` + `TwoFactorController` implement 2FA (`pragmarx/google2fa-laravel`).
- `ForcePasswordChange` middleware gates most authenticated routes, forcing a password reset
  flow before access.
- `PreventXSS` sanitizes input globally.

### Front-end build
Assets are built with **Laravel Mix** (`webpack.mix.js`), not Vite — `mix.js()` /
`mix.postCss()` compile `resources/js/app.js` and `resources/css/app.css`, and a long list of
legacy jQuery-era plugins (Stisla's dependencies: Select2, DataTables, SweetAlert,
Summernote, FullCalendar, Dropzone, etc.) are copied as-is from `node_modules` into
`public/library/<plugin>` rather than bundled — Blade views reference them directly from
`public/library/...`, so adding a new Stisla-theme plugin means adding it to the `plugins`
array in `webpack.mix.js`, not importing it in `app.js`.

### Livewire
Present but lightly used (`livewire/livewire`, `rappasoft/laravel-livewire-tables`,
`wireui/wireui`) — currently just `app/Livewire/BanksTable.php`. Most CRUD screens are
traditional Blade + controller, not Livewire components.

### Locale/timezone
App timezone is `Asia/Makassar` (`config/app.php`) — relevant for attendance/roster time
calculations. Default locale is `en`, but variable names, comments, and some UI strings
throughout the codebase are in Indonesian (this is an Indonesian company's internal HR system).
