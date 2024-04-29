<?php
include 'config.php';


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
   exit; 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   
</head>
<body>

<header class="header">
   
</header>

</body>
</html>
