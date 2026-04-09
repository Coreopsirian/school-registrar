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
