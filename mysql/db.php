<?php

//initialize variables
$email = "";
$password = "";
$email_err =  "";
$password_err = "";
// database connection
$conn = new mysqli("localhost", "root", "", "school-registrar");

if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
//for processing form submissionn
if(isset($_POST['submit'])){
     $email = trim($_POST['email']);
     $password = trim($_POST['password']);

     //check for empty values
     if(empty($email)){
          $email_err = "Please enter your email";
     }
     elseif(empty($password)){
          $password_err = "Please enter your password";
     }
     else{
          //process inputs
     }

}

?>