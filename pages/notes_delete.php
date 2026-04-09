<?php
include('../mysql/db.php');
session_start();
if (!isset($_SESSION['name'])) { http_response_code(401); exit(); }

$data    = json_decode(file_get_contents('php://input'), true);
$id      = intval($data['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM notes WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
echo json_encode(['success' => true]);