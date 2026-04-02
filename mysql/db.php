<?php
$email_err = $pasword_err = "";
$conn = new mysqli("localhost", "root", "", "school-registrar");
if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
if(isset($_POST['submit'])){
     $email = trim($_POST['email']);
     $password = trim($_POST['password']);
}
//check for empty values
if(empty($email)){
     $email_err = "Please enter your email";
}
elseif(empty($email)){
     $password_err = "Please enter your password";
}
else{
     //process inputs
     
}
?>