<<<<<<< HEAD
<!-- Run these SQL statements in phpMyAdmin or MySQL CLI -->

<!-- DATABASE -->
CREATE DATABASE IF NOT EXISTS school_registrar;
USE school_registrar;

<!-- NOTES TABLE -->
CREATE TABLE IF NOT EXISTS notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL DEFAULT 'Untitled Note',
  body TEXT,
  category ENUM('General', 'Academic', 'Meeting', 'Concern') DEFAULT 'General',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

<!-- ADD ARCHIVE COLUMN TO STUDENTS (run if table already exists) -->
ALTER TABLE students ADD COLUMN IF NOT EXISTS is_archived TINYINT(1) DEFAULT 0;

<!-- TEACHERS TABLE -->
CREATE TABLE IF NOT EXISTS teachers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  photo VARCHAR(255),
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100),
  email VARCHAR(150),
  contact_number VARCHAR(20),
  subject VARCHAR(100),
  department VARCHAR(100),
  is_archived TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

<!-- TEACHER ATTENDANCE TABLE -->
CREATE TABLE IF NOT EXISTS teacher_attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT NOT NULL,
  date DATE NOT NULL,
  status ENUM('present', 'absent', 'late') DEFAULT 'present',
  remarks VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
  UNIQUE KEY unique_attendance (teacher_id, date)
);


<!-- USER ROLES — run these to add role-based access control -->

<!-- 1. Add role and is_active columns to existing users table -->
ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('superadmin', 'registrar') NOT NULL DEFAULT 'registrar';
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1;

<!-- 2. Seed the first superadmin account -->
<!-- Password below is: Admin@1234  — CHANGE THIS after first login -->
INSERT INTO users (name, email, password, role, is_active)
VALUES (
  'Super Admin',
  'superadmin@school.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'superadmin',
  1
) ON DUPLICATE KEY UPDATE role='superadmin';
=======
<!-- query -->

CREATE DATABASE IF NOT EXISTS school_registrar;
USE school_registrar;

 INSERT INTO sections (name, grade_level_id) VALUES
('Newton', 1),
('Einstein', 2),
('Curie', 3),
('Franklin', 4);
<!-- option value for dropdown must match the id in the database -->
>>>>>>> 601376344b56fb29ab0efc06d2df45c88d0a2dd4
