<?php
$conn = new mysqli("localhost", "root", "", "school-registrar");
if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

?>