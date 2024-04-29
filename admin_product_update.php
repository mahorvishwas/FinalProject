<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
};

// Initialize $message as an empty array
$message = array();

if (isset($_POST['update_product'])) {

    $pid = $_POST['pid'];
    $name = $_POST['name'];
    $name = preg_replace("/[^a-zA-Z0-9\s\-_.,?!]/", "", $name);

    $price = $_POST['price'];
    $price = filter_var($price, FILTER_SANITIZE_NUMBER_INT);

    $old_image = $_POST['old_image'];
    $image = $_FILES['image']['name'];
    $image = basename($_FILES['image']['name']);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . $image;


    $mysqli = new mysqli($servername, $username, $password, $dbname);


    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $update_product = $mysqli->prepare("UPDATE `products` SET name = ?, price = ? WHERE id = ?");
    $update_product->bind_param("ssi", $name, $price, $pid);
    $update_product->execute();
    $update_product->close();

    echo 'product updated successfully!';

    if (!empty($image)) {
        if ($image_size > 2000000) {
            // Push message into $message array
            $message[] = 'image size is too large!';
        } else {
            $update_image = $mysqli->prepare("UPDATE `products` SET image = ? WHERE id = ?");
            $update_image->bind_param("si", $image, $pid);
            $update_image->execute();
            $update_image->close();

            $upload_directory = 'pm/uploaded_img/';

            if (!is_dir($upload_directory)) {
                mkdir($upload_directory, 0755, true);
            }

            if (move_uploaded_file($image_tmp_name, $upload_directory . $image)) {
                if (file_exists($upload_directory . $old_image)) {
                    unlink($upload_directory . $old_image);
                    // Push message into $message array
                    $message[] = 'Image updated successfully!';
                } else {
                    // Push message into $message array
                    $message[] = 'Old image not found!';
                }
            } else {
                // Push message into $message array
                $message[] = 'Failed to move uploaded image!';
            }
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
    <title>update product</title>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

    <?php include 'admin_header.php' ?>

    <section class="update-product">

        <h1 class="heading">update product</h1>

        <?php
        $update_id = $_GET['update'];

        $mysqli = new mysqli($servername, $username, $password, $dbname);


        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }


        $select_products = $mysqli->prepare("SELECT * FROM `products` WHERE id = ?");
        $select_products->bind_param("i", $update_id);
        $select_products->execute();
        $result = $select_products->get_result();

        if ($result->num_rows > 0) {
            while ($fetch_products = $result->fetch_assoc()) {
        ?>
                <form action="" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                    <input type="hidden" name="old_image" value="<?= $fetch_products['image']; ?>">
                    <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                    <input type="text" class="box" required maxlength="100" placeholder="enter product name" name="name" value="<?= $fetch_products['name']; ?>">
                    <input type="number" min="0" class="box" required max="9999999999" placeholder="enter product price" onkeypress="if(this.value.length == 10) return false;" name="price" value="<?= $fetch_products['price']; ?>">
                    <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
                    <div class="flex-btn">
                        <input type="submit" value="update product" class="btn" name="update_product">
                        <a href="admin_products.php" class="option-btn">go back</a>
                    </div>
                </form>

        <?php
            }
        } else {
            echo '<p class="empty">no product found!</p>';
        }


        $mysqli->close();
        ?>

        <!-- Display messages -->
        <?php foreach ($message as $msg) : ?>
            <p><?= $msg ?></p>
        <?php endforeach; ?>

    </section>

    <script src="js/admin_script.js"></script>

</body>

</html>