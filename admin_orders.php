<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_POST['update_payment'])) {

   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $payment_status = filter_var($payment_status, FILTER_SANITIZE_STRING);


   $mysqli = new mysqli($servername, $username, $password, $dbname);


   if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
   }


   $update_payment = $mysqli->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_payment->bind_param("si", $payment_status, $order_id);
   $update_payment->execute();
   $update_payment->close();


   $mysqli->close();

   $message[] = 'payment status updated!';
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];


   $mysqli = new mysqli($servername, $username, $password, $dbname);


   if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
   }


   $delete_order = $mysqli->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->bind_param("i", $delete_id);
   $delete_order->execute();
   $delete_order->close();


   $mysqli->close();

   header('location:admin_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>orders</title>


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">


   <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

   <?php include 'admin_header.php' ?>

   <section class="orders">

      <h1 class="heading">placed orders</h1>

      <div class="box-container">

         <?php

         $mysqli = new mysqli($servername, $username, $password, $dbname);


         if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
         }


         $select_orders = $mysqli->query("SELECT * FROM `orders`");

         if ($select_orders->num_rows > 0) {
            while ($fetch_orders = $select_orders->fetch_assoc()) {
         ?>
               <div class="box">
                  <p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
                  <p> name : <span><?= $fetch_orders['name']; ?></span> </p>
                  <p> number : <span><?= $fetch_orders['number']; ?></span> </p>
                  <p> address : <span><?= $fetch_orders['address']; ?></span> </p>
                  <p> total products : <span><?= $fetch_orders['total_products']; ?></span> </p>
                  <p> total price : <span><?= $fetch_orders['total_price']; ?></span> </p>
                  <p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
                  <form action="" method="post">
                     <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                     <select name="payment_status" class="select">
                        <option selected disabled><?= $fetch_orders['payment_status']; ?></option>
                        <option value="pending">pending</option>
                        <option value="completed">completed</option>
                     </select>
                     <div class="flex-btn">
                        <input type="submit" value="update" class="option-btn" name="update_payment">
                        <a href="admin_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('delete this order?');">delete</a>
                     </div>
                  </form>
               </div>
         <?php
            }
         } else {
            echo '<p class="empty">no orders placed yet!</p>';
         }


         $mysqli->close();
         ?>

      </div>

   </section>

   <script src="js/admin_script.js"></script>

</body>

</html>