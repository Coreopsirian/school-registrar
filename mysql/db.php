<?php

// database connection
$conn = new mysqli("localhost", "root", "", "school_registrar");

if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

?>