  
<?php
session_start();
require_once './mysql/db.php';  //defines conenction
//initialize variables
$email = "";
$password = "";
$email_err =  "";
$password_err = "";


//for processing form submissionn
if(isset($_POST['submit']))
{
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
          $sql = "select * from users where email = ?";
          $stmt = $conn->prepare($sql);
          $stmt -> bind_param("s", $email);
          //statement execute
          $stmt ->execute();

          $result = $stmt->get_result();
          //check number of rows
          if($result->num_rows > 0){
            //email is correct
            $row =$result->fetch_assoc(); //gets pass from db
            $db_password = $row['password'];
            if (password_verify($password,$db_password)) {
              //former is the types password, cross check if same on db
              $_SESSION['name'] = $row["name"];
              header("location:./pages/dashboard.php"); //redirect to the dashboard

            }
            else{
              $password_err = "Incorrect password";
            }
          }
          else{
            $email_err = "Email is not registered";
          }
    }

}

?>
  
  
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>COJ Registrar Portal — Login</title>
    <link rel="icon" type="image/x-icon" href="./images/COJ.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700;1,800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/login.css">
  </head>
  <body>

    <div class="card">

      <!-- LEFT PANEL -->
      <div class="left">

        <h1>Manage your<br>school with</h1>
        <h1 class="accent">ease</h1>
        <p>Access your student's enrollment<br>records and student data in an<br>organized and secured way.</p>
      </div>

      <!-- RIGHT PANEL -->
      <div class="right">
        <img src="./images/COJ.png" class="logo" alt="COJ Logo">
        <h2>Welcome back, Admin!</h2>
        <p class="subtitle">REGISTRAR PORTAL</p>

        <form id="login-form" method="POST" action="">
          <div class="field">
            <label for="email">Email Address</label>
            <div class="input-wrap">
              <span><i class="bi bi-envelope"></i></span>
              <input id="email" value="<?=$email?>"  type="email" name="email" placeholder="admin@gmail.com">
             
            </div>
             <div class="text-danger"  style="color:red;font-size:10px;" ><?= $email_err ?></div>
          </div>

          <div class="field">
            <label for="password">Password</label>
            <div class="input-wrap">
              <span><i class="bi bi-lock-fill"></i></span>
              <input id="password" type="password" name="password" placeholder="Enter your password">

            </div>
            <div class="text-danger" style="color:red;font-size:10px;"><?= $password_err ?></div>

          </div>

          <a href="#" class="forgot">Forgot password?</a>

          <button type="submit" class="btn-login" name="submit">Log In</button>

          <p class="signup-link">
            Don't have an account? <a href="signup.html">Sign Up</a>
          </p>
        </form>
      </div>

    </div>

   
  </body>
  </html>
