<?php
include('../mysql/db.php');
session_start();
if (!isset($_SESSION['name'])) { http_response_code(401); exit(); }

// Fallback: look up user_id from email if session is old
if (empty($_SESSION['user_id'])) {
  $r = $conn->query("SELECT id FROM users WHERE name = '" . $conn->real_escape_string($_SESSION['name']) . "' LIMIT 1")->fetch_assoc();
  $_SESSION['user_id'] = $r['id'] ?? 0;
}

$user_id  = $_SESSION['user_id'];
$search   = '%' . ($_GET['search'] ?? '') . '%';
$category = $_GET['category'] ?? '';

if ($category) {
  $stmt = $conn->prepare("SELECT * FROM notes WHERE user_id=? AND category=? AND (title LIKE ? OR body LIKE ?) ORDER BY updated_at DESC");
  $stmt->bind_param("isss", $user_id, $category, $search, $search);
} else {
  $stmt = $conn->prepare("SELECT * FROM notes WHERE user_id=? AND (title LIKE ? OR body LIKE ?) ORDER BY updated_at DESC");
  $stmt->bind_param("iss", $user_id, $search, $search);
}

$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
