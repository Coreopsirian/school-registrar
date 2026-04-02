<?php
$password = "timothy123"; // change this to whatever password you want
$hash = password_hash($password, PASSWORD_BCRYPT);
echo $hash;
?>