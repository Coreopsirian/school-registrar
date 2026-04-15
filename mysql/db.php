<?php

// database connection
$conn = new mysqli("localhost", "root", "", "school_registrar");

if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Ensure consistent charset
$conn->set_charset("utf8mb4");
?>