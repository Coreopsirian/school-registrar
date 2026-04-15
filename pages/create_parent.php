<?php
session_start();
include('../mysql/db.php');
if (!isset($_SESSION['name'])) { header('Location: ../index.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $student_id = intval($_POST['student_id']);
  $name       = trim($_POST['parent_name']);
  $email      = trim($_POST['parent_email']);
  $contact    = trim($_POST['parent_contact'] ?? '');
  $password   = trim($_POST['parent_password']);

  if (empty($name) || empty($email) || empty($password)) {
    header("Location: student_profile.php?id=$student_id&error=" . urlencode("All fields required."));
    exit();
  }

  $hashed = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("INSERT INTO parent_accounts (student_id, name, email, password, contact) VALUES (?,?,?,?,?)");
  $stmt->bind_param("issss", $student_id, $name, $email, $hashed, $contact);

  $stmt->execute()
    ? header("Location: student_profile.php?id=$student_id&success=" . urlencode("Parent account created."))
    : header("Location: student_profile.php?id=$student_id&error=" . urlencode("Email already exists or error: " . $conn->error));
  exit();
}

header("Location: students.php");
exit();
