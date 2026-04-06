<?php
if (isset($_GET["id"])){
    $id = $_GET["id"];


 //for connection
$servername = "localhost";
$email = "root";
$password = "";
$database = "school_registrar";

$conn = new mysqli($servername, $email, $password, $database);

$sql = "DELETE FROM students WHERE id = $id";
$conn-> query($sql);

}
?>