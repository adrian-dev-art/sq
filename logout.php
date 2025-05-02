<?php

session_start();

if(isset($_SESSION['user_id'])) {
    session_destroy();
    session_unset();

    // Redirect to the login page
    header("Location: login.php");
    exit();
} else {
    // If the user is not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}


?>