<?php
session_start();
if (!isset($_SESSION['name'])) {
  header('Location: ../index.php');
  exit();
}

$servername = "localhost";
$email = "root";
$password = "";
$database = "school_registrar";

$conn = new mysqli($servername, $email, $password, $database);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $first_name     = $_POST['first_name'] ?? '';
  $middle_name    = $_POST['middle_name'] ?? '';
  $last_name      = $_POST['last_name'] ?? '';
  $lrn            = $_POST['lrn'] ?? '';
  $grade_level_id = $_POST['grade_level_id'] ?? '';
  $section_id     = $grade_level_id; // one section per grade
  $city           = $_POST['city'] ?? '';
  $contact_number = $_POST['contact_number'] ?? '';
  $student_type   = $_POST['status'] ?? '';
  $photo          = $_FILES['photo']['name'] ?? '';
  $temp           = $_FILES['photo']['tmp_name'] ?? '';

  // Upload photo to folder uploads
  if(!empty($temp)) {
    move_uploaded_file($temp, "uploads/" . $photo);
  }

  // Validate required fields
  if(empty($first_name) || empty($last_name) || empty($lrn) || empty($grade_level_id) || empty($city) || empty($contact_number) || empty($student_type)) {
    header("Location: students.php?error=All fields are required");
    exit;
  }

  // Insert to Databasee
  $sql = "INSERT INTO students (photo, first_name, middle_name, last_name, lrn, grade_level_id, section_id, city, contact_number, student_type) 
          VALUES ('$photo', '$first_name', '$middle_name', '$last_name', '$lrn', '$grade_level_id', '$section_id', '$city', '$contact_number', '$student_type')";

  if(!$conn->query($sql)) {
    header("Location: students.php?error=" . urlencode($conn->error));
    exit;
  }

  header("Location: students.php?success=Student added successfully");
  exit;
}

// 
header("Location: students.php");
exit;
?>