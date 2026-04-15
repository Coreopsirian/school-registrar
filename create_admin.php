<?php
require_once './mysql/db.php';

// Change these before running
$name     = 'Super Admin';
$email    = 'admin@coj.edu.ph';
$password = 'Admin@1234';
$role     = 'superadmin';

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role, is_active) VALUES (?,?,?,?,1)
  ON DUPLICATE KEY UPDATE password=VALUES(password), role=VALUES(role), is_active=1");
$stmt->bind_param("ssss", $name, $email, $hashed, $role);

if ($stmt->execute()) {
  echo "<h2 style='color:green;font-family:sans-serif;'>✅ Account created/updated successfully.</h2>";
  echo "<p style='font-family:sans-serif;'>Email: <strong>$email</strong><br>Password: <strong>$password</strong></p>";
  echo "<p style='font-family:sans-serif;'><a href='index.php'>→ Go to Login</a></p>";
} else {
  echo "<h2 style='color:red;font-family:sans-serif;'>❌ Error: " . $conn->error . "</h2>";
}
?>
