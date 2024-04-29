<?php

include 'config.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
   echo '<a href="logout-user.php"><div class="d-flex gap-2 d-md-flex justify-content-md-end">
   <button class="btn logout btn-primary me-md-2" type="button">Log Out</button>
   
 </div></a>';
   if (!isset($_SESSION['login_success'])) {
      echo "<script>alert('Login successful');</script>";
      $_SESSION['login_success'] = true;
   }
} else {
   $user_id = '';
}


$message = [];

global $select_cart_result;

if (isset($_POST['register'])) {
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, $_POST['pass']);
   $cpass = mysqli_real_escape_string($conn, $_POST['cpass']);

   // Check if username or email already exists
   $select_user_query = "SELECT * FROM `user` WHERE name = ? OR email = ?";
   $select_user_statement = $conn->prepare($select_user_query);
   $select_user_statement->bind_param("ss", $name, $email);
   $select_user_statement->execute();
   $select_user_result = $select_user_statement->get_result();

   if ($select_user_result->num_rows > 0) {
      echo "Username or email already exists!";
   } else {
      if ($pass != $cpass) {
         echo "Confirm password does not match!";
      } else {
         // Hash the password using a stronger hashing algorithm 
         $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

         // Insert user into database
         $insert_user_query = "INSERT INTO `user` (name, email, password) VALUES (?, ?, ?)";
         $insert_user_statement = $conn->prepare($insert_user_query);
         $insert_user_statement->bind_param("sss", $name, $email, $hashed_password);

         //   INSERT statement 
         if ($insert_user_statement) {
            if ($insert_user_statement->execute()) {
               echo "<script>alert('Registered successfully. Please login now.');</script>";
            } else {
               echo "Error:" . $conn->error;
            }


            $insert_user_statement->close();
         } else {
            echo "Error preparing INSERT statement.";
         }
      }
   }


   $select_user_statement->close();
}



if (isset($_POST['update_qty'])) {
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   // Validate and sanitize quantity
   $qty = filter_var($qty, FILTER_VALIDATE_INT);
   if ($qty !== false && $qty > 0) {
      $update_qty = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
      $update_qty->execute([$qty, $cart_id]);
      if ($update_qty) {
         $message[] = 'Cart quantity updated!';
      } else {
         $error[] = 'Failed to update cart quantity!';
      }
   } else {
      $error[] = 'Invalid quantity!';
   }
}

if (isset($_GET['delete_cart_item'])) {
   $delete_cart_id = $_GET['delete_cart_item'];
   $delete_cart_item = $conn->prepare("DELETE FROM cart WHERE id = ?");
   $delete_cart_item->execute([$delete_cart_id]);
   if ($delete_cart_item) {
      header('location:index.php');
      exit; // Ensure no further code execution after redirection
   } else {
      $error[] = 'Failed to delete cart item!';
   }
}

// If you want to display any errors or messages, handle them here
if (isset($error)) {
   foreach ($error as $err) {
      echo $err . '<br>';
   }
}
if (isset($message)) {
   foreach ($message as $msg) {
      echo $msg . '<br>';
   }
}


if (isset($_GET['logout'])) {
   session_unset();
   session_destroy();
   header('location:index.php');
}

if (isset($_POST['add_to_cart'])) {

   if ($user_id == '') {
      $message[] = 'please login first!';
   } else {

      $pid = $_POST['pid'];
      $name = $_POST['name'];
      $price = $_POST['price'];
      $image = $_POST['image'];
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_NUMBER_INT);

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
      $select_cart->bind_param("is", $user_id, $name);
      $select_cart->execute();
      $select_cart_result = $select_cart->get_result();

      if ($select_cart_result->num_rows > 0) {
         $message[] = 'already added to cart';
      } else {
         $insert_cart_query = "INSERT INTO `cart` (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)";
         $insert_cart_statement = $conn->prepare($insert_cart_query);
         $insert_cart_statement->bind_param("iisdis", $user_id, $pid, $name, $price, $qty, $image);
         $insert_cart_statement->execute();

         if ($insert_cart_statement->affected_rows > 0) {
            $message[] = 'added to cart!';
         } else {
            $message[] = 'Failed to add to cart!';
         }

         $insert_cart_statement->close();
      }
   }
}



if (isset($_POST['order'])) {

   if ($user_id == '') {
      $message[] = 'please login first!';
   } else {
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
      $number = $_POST['number'];
      $number = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
      $address = 'flat no.' . $_POST['flat'] . ', ' . $_POST['street'] . ' - ' . $_POST['pin_code'];
      $allowed_methods = array("cash on delivery", "credit card", "paytm", "paypal");
      if (in_array($_POST['method'], $allowed_methods)) {
         $method = $_POST['method'];
      } else {
      }
   }
   $total_products = $_POST['add_to_cart'];

   $select_cart_query = "SELECT * FROM cart WHERE user_id = ?";
   $select_cart_statement = mysqli_prepare($conn, $select_cart_query);
   mysqli_stmt_bind_param($select_cart_statement, "i", $user_id);
   mysqli_stmt_execute($select_cart_statement);
   $select_cart_result = mysqli_stmt_get_result($select_cart_statement);

   if (mysqli_num_rows($select_cart_result) > 0) {
      $insert_order_query = "INSERT INTO orders (user_id, name, number, method, address, total_products, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)";
      $insert_order_statement = mysqli_prepare($conn, $insert_order_query);
      mysqli_stmt_bind_param($insert_order_statement, "issdssd", $user_id, $name, $number, $method, $address, $total_products, $total_price);
      mysqli_stmt_execute($insert_order_statement);

      if (mysqli_stmt_affected_rows($insert_order_statement) > 0) {
         $delete_cart_query = "DELETE FROM cart WHERE user_id = ?";
         $delete_cart_statement = mysqli_prepare($conn, $delete_cart_query);
         mysqli_stmt_bind_param($delete_cart_statement, "i", $user_id);
         mysqli_stmt_execute($delete_cart_statement);

         if (mysqli_stmt_affected_rows($delete_cart_statement) > 0) {
            echo 'Order placed successfully!';
         } else {
            echo 'Failed to delete cart items!';
         }
      } else {
         echo 'Failed to place order!';
      }
   } else {
      echo 'Your cart is empty!';
   }

   mysqli_stmt_close($select_cart_statement);
   mysqli_stmt_close($insert_order_statement);
   mysqli_stmt_close($delete_cart_statement);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>FoodFun We</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="css/style.css">
   <style>
      .logout {
         background-color: red;
         width: 100px;
         height: 30px;
         font-size: 15px;
         padding: 0px;
      }
   </style>

</head>

<body>

   <!-- header-->

   <header class="header">

      <section class="flex">

         <a href="#home" class="logo">FoodFun</a>

         <nav class="navbar">
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#menu">Menu</a>
            <a href="#order">Order</a>
            <a href="#faq">FAQ</a>
         </nav>

         <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
            <div id="order-btn" class="fas fa-box"></div>
            <div id="cart-btn" class="fas fa-shopping-cart"><span></span></div>
         </div>

      </section>

   </header>

   <!-- header-->

   <div class="user-account">

      <section>

         <div id="close-account"><span>close</span></div>

         <div class="user">
            <p><span>you are not logged in now!</span></p>
         </div>

         <div class="display-orders">
            <p>pizza-1 <span>( 149/- x 2 )</span></p>
            <p>pizza 03 <span>( 99/- x 1 )</span></p>
            <p>pizza 06 <span>( 49/- x 4 )</span></p>
            <p>pizza 07 <span>( 249/- x 1 )</span></p>
         </div>
         <div class="flex">

            <form action="user_login.php" method="post">
               <h3>login now</h3>
               <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
               <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20">
               <input type="submit" value="login now" name="login" class="btn">
            </form>

            <form action="index.php" method="POST">
               <h3>Register Now</h3>
               <input type="text" name="name" required class="box" placeholder="Enter your username" maxlength="20">
               <input type="email" name="email" required class="box" placeholder="Enter your email" maxlength="50">
               <input type="password" name="pass" required class="box" placeholder="Enter your password" maxlength="20">
               <input type="password" name="cpass" required class="box" placeholder="Confirm your password" maxlength="20">
               <input type="submit" value="Register Now" name="register" class="btn">
            </form>

         </div>

      </section>

   </div>

   <div class="my-orders">

      <section>

         <div id="close-orders"><span>close</span></div>

         <h3 class="title"> my orders </h3>


         <?php
         $select_orders_query = "SELECT * FROM `orders` WHERE user_id = '$user_id'";
         $select_orders_result = mysqli_query($conn, $select_orders_query);
         if (mysqli_num_rows($select_orders_result) > 0) {
            while ($fetch_orders = mysqli_fetch_assoc($select_orders_result)) {
            }
         } else {
            echo '<p class="empty">nothing ordered yet!</p>';
         }
         ?>


      </section>

   </div>

   <div class="shopping-cart">
      <section>
         <div id="close-cart"><span>close</span></div>

         <?php
         $grand_total = 0;
         $select_cart_query = "SELECT * FROM `cart` WHERE user_id = '$user_id'";
         $select_cart_result = mysqli_query($conn, $select_cart_query);
         if (mysqli_num_rows($select_cart_result) > 0) {
            while ($fetch_cart = mysqli_fetch_assoc($select_cart_result)) {
               $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
               $grand_total += $sub_total;
         ?>
               <div class="cart-item">
                  <div class="product-name"><?= $fetch_cart['product_name']; ?></div>
                  <div class="price">₹<?= $fetch_cart['price']; ?></div>
                  <div class="quantity">
                     <input type="number" min="1" value="<?= $fetch_cart['quantity']; ?>" data-cart-id="<?= $fetch_cart['cart_id']; ?>">
                  </div>
                  <div class="subtotal">$<?= $sub_total; ?></div>
               </div>
         <?php
            }
         } else {
            echo '<p class="empty"><span>Your cart is empty!</span></p>';
         }
         ?>
         <div class="cart-total">Grand total: <span>₹<?= $grand_total; ?>/-</span></div>
         <a href="#order" class="btn">Order Now</a>
      </section>
   </div>

   <div class="home-bg">

      <section class="home" id="home">

         <div class="slide-container">

            <div class="slide active">
               <div class="image">
                  <img src="images/home-img-1.png" alt="">
               </div>
               <div class="content">
                  <h3>homemade Pepperoni Pizza</h3>
                  <div class="fas fa-angle-left" onclick="prev()"></div>
                  <div class="fas fa-angle-right" onclick="next()"></div>
               </div>
            </div>

            <div class="slide">
               <div class="image">
                  <img src="images/home-img-2.png" alt="">
               </div>
               <div class="content">
                  <h3>Pizza With Mushrooms</h3>
                  <div class="fas fa-angle-left" onclick="prev()"></div>
                  <div class="fas fa-angle-right" onclick="next()"></div>
               </div>
            </div>

            <div class="slide">
               <div class="image">
                  <img src="images/home-img-3.png" alt="">
               </div>
               <div class="content">
                  <h3>Mascarpone And Mushrooms</h3>
                  <div class="fas fa-angle-left" onclick="prev()"></div>
                  <div class="fas fa-angle-right" onclick="next()"></div>
               </div>
            </div>

         </div>

      </section>

   </div>

   <!-- about -->

   <section class="about" id="about">

      <h1 class="heading">about us</h1>

      <div class="box-container">

         <div class="box">
            <img src="images/about-1.svg" alt="">
            <h3>Made With Love</h3>
            <p>Our commitment to excellence shines through in every bite, as we believe that great pizza is not just
               about taste, but also about the experience. With a dedication to quality and a sprinkle of affection in
               every recipe, we strive to bring joy to every customer's palate</p>
            <a href="#menu" class="btn">our menu</a>
         </div>

         <div class="box">
            <img src="images/about-2.svg" alt="">
            <h3>30 Minutes Delivery</h3>
            <p>Experience the convenience of fast, reliable pizza delivery with FoodFun. Order now and get ready to
               indulge in a slice of perfection,delivered straight FoodFun
               to deliver satisfaction, convenience, and most importantly, deliciousness, all within 30 minutes!</p>
            <a href="#order" class="btn">Order Now </a>
         </div>

         <div class="box">
            <img src="images/about-3.svg" alt="">
            <h3>Share With Friends</h3>
            <p>At FoodFun, we're not just about serving pizza; we're about creating unforgettable
               experiences, one slice at a time. Our passion for crafting the perfect pizza goes beyond the kitchen;
               it's about bringing joy to every customer who walks through our doors.</p>
            <a href="#home" class="btn">Share It</a>
         </div>

      </div>

   </section>

   <!-- about  -->

   <!-- menu  -->

   <section id="menu" class="menu">
    <h1 class="heading">our menu</h1>
    <div class="box-container">
    <?php
$select_products = "SELECT * FROM `products`";
$result = $conn->query($select_products);

if ($result && $result->num_rows > 0) {
    while ($fetch_products = $result->fetch_assoc()) {
?>
        <div class="box">
            <div class="price">₹<?= $fetch_products['price']; ?>/-</div>
            <img src="pm/uploaded_img/<?= $fetch_products['image'] ?>" alt="">
            <div class="name"><?= $fetch_products['name'] ?></div>
            <form action="" method="post">
                <input type="hidden" name="pid" value="<?= $fetch_products['id'] ?>">
                <input type="hidden" name="name" value="<?= $fetch_products['name'] ?>">
                <input type="hidden" name="price" value="<?= $fetch_products['price'] ?>">
                <input type="hidden" name="image" value="<?= $fetch_products['image'] ?>">
                <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
                <input type="submit" class="btn" name="add_to_cart" value="Add to Cart">
            </form>
        </div>
<?php
    }
} else {
    echo '<p class="empty">No products added yet!</p>';
}
?>

    </div>
</section>
<?php 

if (isset($_SESSION['user_id'])) {
   if (isset($_POST['add_to_cart'])) {
       $uid = $_SESSION['user_id'];
       $pid = $_POST['pid'];
       $name = $_POST['name'];
       $price = $_POST['price'];
       $img = $_POST['image'];
       $q = $_POST['qty'];

       // Prepare the SQL statement
       $insert_cart = $conn->prepare("INSERT INTO `cart`(`user_id`, `pid`, `name`, `price`, `quantity`, `image`) VALUES (?, ?, ?, ?, ?, ?)");
       
       // Bind parameters
       $insert_cart->bind_param("iisdis", $uid, $pid, $name, $price, $q, $img);
       
       // Execute the statement
       if ($insert_cart->execute()) {
           // Cart item inserted successfully
           // You can redirect or display a success message here
       } else {
           // Error handling if the insertion fails
           // You can redirect or display an error message here
       }
   }
} else {
   echo "<script>alert('Please login to add items to the cart.');</script>";
}

?>


   <!-- order -->
   <section class="order" id="order">

      <h1 class="heading">order now</h1>

      <form action="" method="post">

         <div class="display-orders">

            <?php
            $grand_total = 0;
            $cart_item = array();
            $user_id = 1; // Replace with the actual user's ID
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->bind_param("i", $user_id);
            $select_cart->execute();
            $result = $select_cart->get_result();
            if ($result->num_rows > 0) {
               while ($fetch_cart = $result->fetch_assoc()) {
                  $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
                  $grand_total += $sub_total;
                  $cart_item[] = $fetch_cart['name'] . ' ( ' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ' ) - ';
                  $total_products = implode($cart_item);
                  echo '<p>' . $fetch_cart['name'] . ' <span>(' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')</span></p>';
               }
            } else {
               echo '<p class="empty"><span>your cart is empty!</span></p>';
            }

            $conn->close();
            ?>

            <div class="grand-total"> grand total : <span>₹<?= $grand_total; ?>/-</span></div>

            <input type="hidden" name="total_products" value="<?= $total_products; ?>">
            <input type="hidden" name="total_price" value="<?= $grand_total; ?>">

            <div class="flex">
               <div class="inputBox">
                  <span>your name :</span>
                  <input type="text" name="name" class="box" required placeholder="enter your name" maxlength="20">
               </div>
               <div class="inputBox">
                  <span>your number :</span>
                  <input type="number" name="number" class="box" required placeholder="enter your number" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;">
               </div>
               <div class="inputBox">
                  <span>payment method</span>
                  <select name="method" class="box">
                     <option value="cash on delivery">cash on delivery</option>
                     <option value="credit card">credit card</option>
                     <option value="paytm">paytm</option>
                     <option value="paypal">paypal</option>
                  </select>
               </div>
               <div class="inputBox">
                  <span>address line 01 :</span>
                  <input type="text" name="flat" class="box" required placeholder="e.g. flat no." maxlength="50">
               </div>
               <div class="inputBox">
                  <span>address line 02 :</span>
                  <input type="text" name="street" class="box" required placeholder="e.g. street name." maxlength="50">
               </div>
               <div class="inputBox">
                  <span>pin code :</span>
                  <input type="number" name="pin_code" class="box" required placeholder="e.g. 123456" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;">
               </div>
            </div>

            <input type="submit" value="order now" class="btn" name="order">

      </form>
   </section>
   <!-- faq -->

   <section class="faq" id="faq">

      <h1 class="heading">FAQ</h1>

      <div class="accordion-container">

         <div class="accordion active">
            <div class="accordion-heading">
               <span>how does it work?</span>
               <i class="fas fa-angle-down"></i>
            </div>
            <p class="accrodion-content">
               Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium
               maxime, doloremque iusto deleniti veritatis quos.
            </p>
         </div>

         <div class="accordion">
            <div class="accordion-heading">
               <span>how long does it take for delivery?</span>
               <i class="fas fa-angle-down"></i>
            </div>
            <p class="accrodion-content">
               Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium
               maxime, doloremque iusto deleniti veritatis quos.
            </p>
         </div>

         <div class="accordion">
            <div class="accordion-heading">
               <span>can I order for huge parties?</span>
               <i class="fas fa-angle-down"></i>
            </div>
            <p class="accrodion-content">
               Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium
               maxime, doloremque iusto deleniti veritatis quos.
            </p>
         </div>

         <div class="accordion">
            <div class="accordion-heading">
               <span>how much protein it contains?</span>
               <i class="fas fa-angle-down"></i>
            </div>
            <p class="accrodion-content">
               Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium
               maxime, doloremque iusto deleniti veritatis quos.
            </p>
         </div>


         <div class="accordion">
            <div class="accordion-heading">
               <span>is it cooked with oil?</span>
               <i class="fas fa-angle-down"></i>
            </div>
            <p class="accrodion-content">
               Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium
               maxime, doloremque iusto deleniti veritatis quos.
            </p>
         </div>

      </div>

   </section>

   <!-- faq  -->

   <!-- footer -->

   <section class="footer">

      <div class="box-container">

         <div class="box">
            <i class="fas fa-phone"></i>
            <h3>phone number</h3>
            <p>8269469708</p>
            <p>0751-111-111</p>
         </div>

         <div class="box">
            <i class="fas fa-map-marker-alt"></i>
            <h3>our address</h3>
            <p>Gwalior, india - 474011</p>
         </div>

         <div class="box">
            <i class="fas fa-clock"></i>
            <h3>opening hours</h3>
            <p>10:00am to 11:00pm</p>
         </div>

         <div class="box">
            <i class="fas fa-envelope"></i>
            <h3>email address</h3>
            <p>vishwasmahor@gmail.com</p>
            <p>tarunlala377@gmail.com</p>
         </div>

      </div>

      <div class="credit">
         &copy; copyright @ <?= date('Y'); ?> by <span> Vishwas</span> | all rights reserved!
      </div>


   </section>

   <!-- footer -->




















   <script src="js/script.js"></script>

</body>

</html>