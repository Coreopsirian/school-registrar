<?php
$host = 'localhost';
$user = 'root';
$pass = 'password';
$dbname = 'COJ_Database';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database created successfully!<br>";

    $pdo->exec("USE `$dbname`;");

    $sql = "CREATE TABLE IF NOT EXISTS students (

        id INT AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(50) NOT NULL,
        grade_and_section VARCHAR(100) NOT NULL UNIQUE,
        city VARCHAR(255) NOT NULL,
        student_status VARCHAR(255) NOT NULL,
        student_action VARCHAR(255) NOT NULL,
        photo varchar(255)
    ) ENGINE=InnoDB;";

    $pdo->exec($sql);
    echo "Table 'users' created successfully!";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>