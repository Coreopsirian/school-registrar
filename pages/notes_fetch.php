


<?php
include('../mysql/db.php');
session_start();
if (!isset($_SESSION['name'])) { http_response_code(401); exit(); }

$user_id = $_SESSION['user_id'];
$search  = '%' . ($_GET['search'] ?? '') . '%';

$stmt = $conn->prepare("SELECT * FROM notes WHERE user_id=? AND (title LIKE ? OR body LIKE ? OR category LIKE ?) ORDER BY updated_at DESC");
$stmt->bind_param("isss", $user_id, $search, $search, $search);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode($rows);