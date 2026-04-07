<!-- query -->

CREATE DATABASE IF NOT EXISTS school_registrar;
USE school_registrar;

 INSERT INTO sections (name, grade_level_id) VALUES
('Newton', 1),
('Einstein', 2),
('Curie', 3),
('Franklin', 4);
<!-- option value for dropdown must match the id in the database -->
