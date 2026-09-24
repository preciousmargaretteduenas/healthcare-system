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
