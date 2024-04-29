<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>dashboard</title>

 
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">


   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>

<?php include 'admin_header.php' ?>

<section class="dashboard">

   <h1 class="heading">dashboard</h1>

   <div class="box-container">

      <div class="box">
         <?php
            $total_pendings = 0;
           
            $mysqli = new mysqli($servername, $username, $password, $dbname);

            
            if ($mysqli->connect_error) {
               die("Connection failed: " . $mysqli->connect_error);
            }

 
            $select_pendings = $mysqli->query("SELECT * FROM `orders` WHERE payment_status = 'pending'");
            if($select_pendings->num_rows > 0){
               while($fetch_pendings = $select_pendings->fetch_assoc()){
                  $total_pendings += $fetch_pendings['total_price'];
               }
            }
         ?>
         <h3>$<?= $total_pendings; ?>/-</h3>
         <p>total pendings</p>
         <a href="admin_orders.php" class="btn">see orders</a>
      </div>

      <div class="box">
         <?php
            $total_completes = 0;

     
            $select_completes = $mysqli->query("SELECT * FROM `orders` WHERE payment_status = 'completed'");
            if($select_completes->num_rows > 0){
               while($fetch_completes = $select_completes->fetch_assoc()){
                  $total_completes += $fetch_completes['total_price'];
               }
            }
         ?>
         <h3>$<?= $total_completes; ?>/-</h3>
         <p>completed orders</p>
         <a href="admin_orders.php" class="btn">see orders</a>
      </div>

      <div class="box">
         <?php
         
            $select_orders = $mysqli->query("SELECT * FROM `orders`");
            $number_of_orders = $select_orders->num_rows;
         ?>
         <h3><?= $number_of_orders; ?></h3>
         <p>orders placed</p>
         <a href="admin_orders.php" class="btn">see orders</a>
      </div>

      <div class="box">
         <?php
      
            $select_products = $mysqli->query("SELECT * FROM `products`");
            $number_of_products = $select_products->num_rows;
         ?>
         <h3><?= $number_of_products; ?></h3>
         <p>products added</p>
         <a href="admin_products.php" class="btn">see products</a>
      </div>

      <div class="box">
         <?php
            $select_users = $mysqli->query("SELECT * FROM `user`");
            $number_of_users = $select_users->num_rows;
         ?>
         <h3><?= $number_of_users; ?></h3>
         <p>normal users</p>
         <a href="users_accounts.php" class="btn">see users</a>
      </div>

      <div class="box">
         <?php
           
            $select_admins = $mysqli->query("SELECT * FROM `admin`");
            $number_of_admins = $select_admins->num_rows;
         ?>
         <h3><?= $number_of_admins; ?></h3>
         <p>admin users</p>
         <a href="admin_accounts.php" class="btn">see admins</a>
      </div>

      <?php
         
         $mysqli->close();
      ?>

   </div>

</section>

<script src="js/admin_script.js"></script>

</body>
</html>
