<?php
session_start();
if (!isset($_SESSION['name'])) {
  header('Location: ../index.php');
  exit();
}

$servername = "localhost";
$email      = "root";
$password   = "";
$database   = "school_registrar";

$conn = new mysqli($servername, $email, $password, $database);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id             = intval($_POST['id']              ?? 0);
  $first_name     = trim($_POST['first_name']        ?? '');
  $middle_name    = trim($_POST['middle_name']        ?? '');
  $last_name      = trim($_POST['last_name']          ?? '');
  $lrn            = trim($_POST['lrn']                ?? '');
  $grade_level_id = intval($_POST['grade_level_id']   ?? 0);
  $section_id     = intval($_POST['section_id']       ?? 0);
  $city           = trim($_POST['city']               ?? '');
  $contact_number = trim($_POST['contact_number']     ?? '');
  $student_type   = trim($_POST['status']             ?? '');
  $existing_photo = trim($_POST['existing_photo']     ?? '');

  // Keep old photo if no new one uploaded
  if (!empty($_FILES['photo']['name'])) {
    $photo = $_FILES['photo']['name'];
    move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photo);
  } else {
    $photo = $existing_photo;
  }

  // Validate required fields
  if (empty($first_name) || empty($last_name) || empty($lrn) ||
      empty($grade_level_id) || empty($city) || empty($contact_number) || empty($student_type)) {
    header("Location: students.php?error=" . urlencode("All required fields must be filled.") . "&edit_id=$id");
    exit;
  }

  if ($id <= 0) {
    header("Location: students.php?error=" . urlencode("Invalid student ID."));
    exit;
  }

  // Prepared statement UPDATE
  $stmt = $conn->prepare(
    "UPDATE students SET
      photo=?, first_name=?, middle_name=?, last_name=?, lrn=?,
      grade_level_id=?, section_id=?, city=?, contact_number=?, student_type=?
     WHERE id=?"
  );
  $stmt->bind_param(
    "sssssiiissi",
    $photo, $first_name, $middle_name, $last_name, $lrn,
    $grade_level_id, $section_id, $city, $contact_number, $student_type,
    $id
  );

  if ($stmt->execute()) {
    header("Location: students.php?success=" . urlencode("Student updated successfully"));
    exit;
  } else {
    header("Location: students.php?error=" . urlencode("Update failed: " . $stmt->error) . "&edit_id=$id");
    exit;
  }

  $stmt->close();
}

// If accessed directly without POST, redirect back
header("Location: students.php");

//auto open edit modal
<?php if (!empty($_GET['edit_id'])): ?>

<script>
  document.getElementById('edit-modal').classList.add('open');
</script>
<?php endif; ?>

?>