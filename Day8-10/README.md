# Student Registration System

A student registration form for campus orientation, built with PHP, Bootstrap 5, and vanilla JavaScript. Students fill in their details and a profile photo, submit the form, and land on a printable confirmation page. An admin dashboard lets staff review, search, and manage submissions.

## Features

**Registration form**
- Full name, email, mobile, gender, course, and address fields
- Drag-and-drop or click-to-browse profile photo upload with a live preview
- Real-time client-side validation (Bootstrap valid/invalid states) that mirrors the server-side rules, so mistakes are caught before submitting
- CSRF-protected form submission

**Confirmation page**
- Gradient header card summarizing the registration
- Profile photo, course badge, and all submitted details laid out in a responsive card grid
- Print button for a clean, printer-friendly confirmation
- A unique reference ID for each registration

**Server-side (`confirm.php`)**
- Re-validates every field (never trusts the client alone)
- Validates uploaded photos by real MIME type (not just file extension) and caps size at 3MB
- Persists each registration to `data/registrations.json` with an exclusive file lock, so concurrent submissions can't corrupt the store
- Saves photos to `uploads/` with generated filenames (falls back to embedding the image directly if the server can't write files)

**Admin dashboard (`/admin`)**
- Login-protected (session + bcrypt password hash + CSRF)
- Table of all registrations with photo thumbnails, search by name/email/course, and delete support
- `uploads/` is locked down with an `.htaccess` that disables PHP execution, so it can only ever serve images

## Tech stack

- PHP 8 (no framework, no database — flat-file JSON storage)
- Bootstrap 5.3 + Font Awesome 6 (via CDN)
- Vanilla JavaScript (no build step)

## Project structure

```
.
├── index.php              # Registration form
├── confirm.php             # Handles submission, validation, persistence
├── script.js                # Client-side validation + photo drag & drop
├── style.css                # All custom styles
├── includes/
│   └── functions.php        # Shared helpers: storage, CSRF, photo handling
├── admin/
│   ├── config.php            # Admin credentials (change before deploying!)
│   ├── login.php              # Admin login
│   ├── index.php               # Admin dashboard
│   └── logout.php               # Ends the admin session
├── data/                    # registrations.json is created here at runtime
└── uploads/                 # Uploaded profile photos are stored here
```

## Running locally

You need PHP 8+ (no database required).

```bash
php -S localhost:8000
```

Then open `http://localhost:8000` in your browser.

Make sure `data/` and `uploads/` are writable by the PHP process:

```bash
chmod 775 data uploads
```

If they aren't writable, the form still works — photos are embedded directly into the confirmation page and registrations simply won't be saved to the dashboard.

## Admin access

Visit `/admin` and sign in with the demo credentials:

- **Username:** `admin`
- **Password:** `orientation2026`

**Before deploying anywhere real:** generate your own password hash and replace `ADMIN_PASSWORD_HASH` in `admin/config.php`:

```bash
php -r "echo password_hash('your-new-password', PASSWORD_DEFAULT);"
```

## Security notes

- All user-supplied text is escaped with `htmlspecialchars()` before output.
- File uploads are checked by real MIME type via `finfo`, not by file extension or the browser-supplied content type.
- Every state-changing form (registration, admin login, admin delete) carries a CSRF token validated with `hash_equals()`.
- The `uploads/` folder ships with an `.htaccess` that disables PHP execution, so even if an unexpected file ever landed there, it couldn't run as a script.

## Possible next steps

- Move from JSON file storage to SQLite/MySQL for larger scale
- Add email confirmation on successful registration
- Export registrations to CSV from the admin dashboard
- Add pagination to the admin table for large registration counts
