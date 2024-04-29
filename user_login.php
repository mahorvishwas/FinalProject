<?php
include 'config.php';

session_start();

if(isset($_POST['login'])){
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = filter_var($_POST['pass'], FILTER_SANITIZE_NUMBER_INT);

    
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    $select_user = $conn->prepare("SELECT id, password FROM `user` WHERE email = ?");
    $select_user->bind_param("s", $email);
    $select_user->execute();
    $select_user_result = $select_user->get_result();
 
    if (!$select_user_result) {
        $message[] = 'Error executing the query: ' . $conn->error;
    } else {
        
        if ($select_user_result->num_rows > 0) {
            $row = $select_user_result->fetch_assoc();
        
            if ($pass == $row['password']) {
                $_SESSION['user_id'] = $row['id'];
                echo "sjfdks";
                header('location: index.php'); 
                
                exit(); 
            } else {
                echo'[Incorrect password.]';
            }
        } else {
            $message[] = 'No user found.';
        }
    }
}
?>
