<!-- database -->
CREATE DATABASE IF NOT EXISTS school_registrar;
USE school_registrar;


<!-- NOTES TABLE -->
CREATE TABLE notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL DEFAULT 'Untitled Note',
  body TEXT,
  category ENUM('General', 'Academic', 'Meeting', 'Concern') DEFAULT 'General',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);