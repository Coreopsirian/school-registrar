# COJ Catholic Progressive School — Registrar Portal

This is a web-based school registrar management system built to replace manual, paper-based record by providing a secure, organized, and accessible digital system for the school's registrar office.

## Tech Stack

Frontend (HTML, CSS, Bootsrap, Js)
Backend (PHP)
Database (MYSQL, MariaDB)
Charts (Chart.js)
Export (SheetJS)
Tunnel (ngrok)


## Features of the system !

### Authentication & Access Control
- Only Login with email and password with Remember Me (30-day cookie)
- Two roles created: **Superadmin** and **Registrar**

- Superadmin manages user accounts  can add , edit, activate/deactivate users
- Session-based auth on every page with role checks on restricted pages

### Dashboard
- For stats: total teachers, students, new enrollees, present today
- Bar chart which shows students per grade level (Chart.js)
- Section enrollment table
- Recent student registrations
- Today's teacher attendance summary +  attendance rate bar

### Student Records
- Add, edit, archive students with photo upload
- Search feature by name or LRN
- Filter by grade level, enrollment status, and school year
- Pagination (10 per page) with filter persistence
- Export filtered records to CSV
- Student profile page 
- Form data retained on validation error (no data loss on failed submit)
- Input validation: LRN (12 digits), names (letters only), PH contact format

### Teacher Management
- Add, edit, archive teachers with photo upload
- Teacher profile with personal info + monthly attendance history
- Month picker to view any month's attendance records

### Attendance Tracking
- Daily teacher attendance: Present / Absent / Late
- Date picker for viewing/editing any past date
- Optional remarks/comment per teacher
- Summary stats and attendance rate per day

### Reports
- Student enrollment by grade (new vs old breakdown)
- Teacher attendance summary filterable by month
- Can be exported both reports to Excel 

### Notes
- Personal notes per user
- Notes can be searched and filtered by category (General, Academic, Meeting, Concern)

### Archived Records
- Separate tabs for archived students and teachers
- Restore records at any time
- Permanent delete (superadmin only)

### User Management (Superadmin only)
- View all system accounts
- Add new registrar accounts with role assignment
- Edit name, email, role, password
- Activate / deactivate accounts
- Registrar account has access to all tabs except user management


## Database Schema
The database `school_registrar` has  8 tables.
```
users              — system accounts (superadmin, registrar)
school_years       — enrollment periods (e.g. SY 2025-2026)
grade_levels       — Grade 7 to Grade 10
sections           — Newton, Einstein, Curie, Franklin (per grade level) --subject to change
students           — student records with FK to grade, section, school year
teachers           — teacher records
teacher_attendance — daily attendance per teacher (unique per teacher and date)
notes              — personal notes per user account
```

All tables use foreign keys.
 `students` references `grade_levels`, `sections`, and `school_years`. `teacher_attendance` references `teachers` with CASCADE delete.

 ## Security

- All queries use prepared statements to avoid SQL injection
- Passwords hashed with `password_hash()`
- Session-based authentication checked on every page
- Role based access like superadmin-only pages
- Simply designed for **intranet deployment** so sensitive student data stays on-premises
-  In compliance with RA 10173 (Data Privacy Act), we ensure no data exposure by hosting this on localhost and using prepared statements

## Out of Scope/ Limitations

- No student attendance tracking (teacher attendance only)
- No grade/marks recording or report card generation
- No financial/ Payment System
- Sections are currently fixed 



## Setup Instructions

### You need the ff. (XAMPP [Apache + MySQL], PHP)

1. Open your VSCode and go to C:\xampp\htdocs\ 
2. On your VSCode terminal and type git clone https://github.com/novadar-star/school-registrar.git
3.Extract the folder and make sure you're inside this (C:\xampp\htdocs\school-registrar\)
3. Start Apache and MySQL in XAMPP.
4. Click shell and type mysql -u root. Type use school_registrar.
4. Open the shell terminal of XAMPP and copy all the SQL statements.
5. Open your browser (http://localhost/school-registrar/)
6. You may log in using superadmin or registrar:
```
Super Admin
Email:    superadmin@gmail.com
Password: Admin@1234

Registrar
Email: timothy@gmail.com
Password: timothy
```

- You can change the password and email via user tab under superadmin account

## To host the system via secure tunnel from the localhost

## Type this to run ngrok
- ngrok http 80
- URL: https://xxxx.ngrok-free.app/school-registrar/pages/dashboard.php


## Team

- Garra, Aaron James (frontend, design)
- Lastrilla, Timothy James (backend, database)
- Moloboco, Juan Gabriel (frontend, backend)
- Singh, Gurjindier (frontend, design)
- Raduban, James Adrian (backend, database)
- Sumanting, Darla Nova  (design, backend, database)