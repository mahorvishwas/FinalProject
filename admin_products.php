<?php

include 'config.php';

session_start();

$message = []; // Initialize $message as an empty array

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_POST['add_product'])){
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT); // Assuming price is numeric
 
    $image_name = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_size = $_FILES['image']['size'];
    $image_folder = 'pm/uploaded_img/' . $image_name;
 
    // Check if product name already exists
    $select_product = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
    $select_product->bind_param("s", $name);
    $select_product->execute();
    $result = $select_product->get_result();
 
    if($result->num_rows > 0){
       $message[] = 'Product name already exists!';
    }else{
       if($image_size > 2000000){
          $message = 'Image size is too large!';
       }else{
          // Insert new product
          $insert_product = $conn->prepare("INSERT INTO `products`(name, price, image) VALUES(?,?,?)");
          $insert_product->bind_param("sss", $name, $price, $image_name);
          if($insert_product->execute()){
             // Move uploaded image
             if(move_uploaded_file($image_tmp_name, $image_folder)){
                $message[] = 'New product added!';
             } else {
                $message[] = 'Failed to move uploaded file!';
             }
          } else {
             $message[] = 'Failed to add product to database!';
          }
       }
    }
 }
 

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT image FROM `products` WHERE id = ?");
   $delete_product_image->bind_param("i", $delete_id);
   $delete_product_image->execute();
   $result_image = $delete_product_image->get_result();
   $fetch_delete_image = $result_image->fetch_assoc();
   unlink('uploaded_img/'.$fetch_delete_image['image']);
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->bind_param("i", $delete_id);
   $delete_product->execute();
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->bind_param("i", $delete_id);
   $delete_cart->execute();
   header('location:admin_products.php');

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom admin style link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>

<?php include 'admin_header.php' ?>

<section class="add-products">

   <h1 class="heading">add product</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <input type="text" class="box" required maxlength="100" placeholder="enter product name" name="name">
      <input type="number" min="0" class="box" required max="9999999999" placeholder="enter product price" onkeypress="if(this.value.length == 10) return false;" name="price">
      <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
      <input type="submit" value="add product" class="btn" name="add_product">
   </form>

</section>

<section class="show-products">

   <h1 class="heading">products added</h1>

   <div class="box-container">

   <?php
      $select_products = $conn->prepare("SELECT * FROM `products`");
      $select_products->execute();
      $result_products = $select_products->get_result();
      if($result_products->num_rows > 0){
         while($fetch_products = $result_products->fetch_assoc()){ 
            
   ?>
   <div class="box">
      <div class="price"><span><?= $fetch_products['price']; ?></span>/-</div>
      <img src="pm/uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div class="flex-btn">
         <a href="admin_product_update.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
         <a href="admin_products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
   ?>
   
   </div>

</section>



<script src="js/admin_script.js"></script>

</body>
</html>
