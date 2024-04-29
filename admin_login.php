<?php

include 'config.php';

session_start();

if(isset($_POST['login'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_SPECIAL_CHARS)   ;

   $mysqli = new mysqli($servername, $username, $password, $dbname);

   
   if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
   }

   
   $select_admin = $mysqli->prepare("SELECT * FROM `admin` WHERE name = ? AND password = ?");
   $select_admin->bind_param("ss", $name, $pass);
   $select_admin->execute();
   $result = $select_admin->get_result();

   if($result->num_rows > 0){
      $row = $result->fetch_assoc();
      $_SESSION['admin_id'] = $row['id'];
      header('location:admin_page.php');
   }else{
      $message[] = 'incorrect username or password!';
   }

   
   $select_admin->close();
   $mysqli->close();

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>admin login</title>

   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
<?php
   if(isset($messages)){
      foreach($messages as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<section class="form-container">

   <form action="" method="post">
      <h3>login now</h3>
      <p>default username = <span>admin</span> & password = <span>111</span></p>
      <input type="text" name="name" required placeholder="enter your username" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="pass" required placeholder="enter your password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="login now" class="btn" name="login">
   </form>

</section>
   
</body>
</html>
