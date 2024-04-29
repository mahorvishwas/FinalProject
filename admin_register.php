<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
};

if (isset($_POST['register'])) {

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_SPECIAL_CHARS);
    $cpass = sha1($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_SPECIAL_CHARS);

    $mysqli = new mysqli($servername, $username, $password, $dbname);


    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }


    $select_admin = $mysqli->prepare("SELECT * FROM `admin` WHERE name = ?");
    $select_admin->bind_param("s", $name);
    $select_admin->execute();
    $result = $select_admin->get_result();

    if ($result->num_rows > 0) {
        $message[] = 'username already exists!';
    } else {
        if ($pass != $cpass) {
            $message[] = 'confirm password not matched!';
        } else {
           
            $insert_admin = $mysqli->prepare("INSERT INTO `admin`(name, password) VALUES(?,?)");
            $insert_admin->bind_param("ss", $name, $cpass);
            $insert_admin->execute();
            $message[] = 'new admin registered successfully!';
        }
    }

 
    $mysqli->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register admin</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

  
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>

<?php include 'admin_header.php' ?>

<section class="form-container">

   <form action="" method="post">
      <h3>register now</h3>
      <input type="text" name="name" required placeholder="enter your username" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="pass" required placeholder="enter your password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="cpass" required placeholder="confirm your password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="register now" class="btn" name="register">
   </form>

</section>


<script src="js/admin_script.js"></script>

</body>
</html>
