# Pag-Asa Healthcare Center System

Web-Based Healthcare Service Management System for Barangay Pag-Asa, Binangonan, Rizal.

## Main features

- Resident appointment requests
- Appointment tracking code
- Resident appointment/queue status checking
- Staff-only login
- Staff dashboard
- Patient records
- Appointment confirmation/cancellation
- Appointment check-in
- Queue numbers (Q-001, Q-002, ...)
- Waiting / Serving / Completed queue statuses
- No medicine inventory/tracker

## Important: GitHub Pages vs XAMPP

The PHP and MariaDB parts of this project **do not run on GitHub Pages**.

Use XAMPP for the working system:

1. Install/start Apache and MySQL in XAMPP.
2. Put this repository in:
   `C:\xampp\htdocs\healthcare-system`
3. If this is a new database, import `database.sql` using phpMyAdmin.
4. If you already created the old database, import `database_update.sql` instead.
5. Open:
   `http://localhost/healthcare-system/`

## Staff test login

Username: `staff`

Password: `staff123`

Change this password before using the system with real information.

## Resident flow

Home → Book Appointment → receive tracking code → Check Appointment Status.

## Staff flow

Staff Login → Dashboard → Appointments → Confirm → Check In → Queue → Call Next → Complete.

## Privacy

This is a school prototype. Do not put real patient information, passwords, or other sensitive data into this public GitHub repository.
\n\n## Automatic tracking-code email (PHP backend)\n\nAppointment submissions can send an email with the tracking code after the database save, using Gmail SMTP through PHPMailer. This only works on PHP hosting/XAMPP configured for the backend, not GitHub Pages.\n\n1. Install Composer on the PHP server. In the project folder run: `composer require phpmailer/phpmailer`.\n2. Copy `smtp_config.example.php` to `smtp_config.php` (the real config is ignored by Git).\n3. In your Google account, enable 2-Step Verification and create a Google App Password. Use the App Password in `smtp_config.php`, not your normal Gmail password.\n4. Set your Gmail address and App Password in that local config. Never upload the secret.\n5. Use the PHP appointment form via `http://localhost/healthcare-system/` (or your PHP-hosted URL). The submission page indicates whether the email was sent.\n\nIf email is not configured, the appointment still saves and displays the tracking code; it reports that email delivery is unavailable.\n