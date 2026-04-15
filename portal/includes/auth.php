<?php
session_name('parent_session');
session_start();
if (!isset($_SESSION['parent_id'])) {
  header('Location: ../portal/login.php'); exit();
}
require_once '../mysql/db.php';
$student_id  = $_SESSION['student_id'];
$parent_name = $_SESSION['parent_name'];
