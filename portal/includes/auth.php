<?php
session_name('parent_session');
session_start();
if (!isset($_SESSION['parent_id'])) {
  header('Location: ../portal/login.php'); exit();
}
require_once '../mysql/db.php';

$parent_id   = $_SESSION['parent_id'];
$parent_name = $_SESSION['parent_name'];

// Fetch linked students using parameterized query — no direct table reference in session
if (empty($_SESSION['student_id'])) {
  $lnk_stmt = $conn->prepare("SELECT student_id FROM parent_student_links WHERE parent_id = ? LIMIT 1");
  $lnk_stmt->bind_param("i", $parent_id);
  $lnk_stmt->execute();
  $first = $lnk_stmt->get_result()->fetch_assoc();
  if ($first) {
    $_SESSION['student_id'] = $first['student_id'];
  } else {
    $_SESSION['student_id'] = 0;
  }
}
$student_id = intval($_SESSION['student_id']);
