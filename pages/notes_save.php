<?php
include('../mysql/db.php');
session_start();
if (!isset($_SESSION['name'])) { http_response_code(401); exit(); }

$data = json_decode(file_get_contents('php://input'), true);
$user_id  = $_SESSION['user_id']; // make sure your session stores this
$title    = trim($data['title'])    ?? 'Untitled Note';
$body     = trim($data['body'])     ?? '';
$category = $data['category']       ?? 'General';
$id       = intval($data['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE notes SET title=?, body=?, category=?, updated_at=NOW() WHERE id=? AND user_id=?");
    $stmt->bind_param("sssii", $title, $body, $category, $id, $user_id);
} else {
    $stmt = $conn->prepare("INSERT INTO notes (user_id, title, body, category) VALUES (?,?,?,?)");
    $stmt->bind_param("isss", $user_id, $title, $body, $category);
}

$stmt->execute();
echo json_encode(['success' => true, 'id' => $id > 0 ? $id : $conn->insert_id]);