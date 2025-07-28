<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['receiver_id']) && !empty($_POST['message'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = (int)$_POST['receiver_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $insert = "INSERT INTO messages (sender_id, receiver_id, message) 
           VALUES ('$sender_id', '$receiver_id', '$message')";
    
    if (mysqli_query($conn, $insert)) {
        header("Location: doctor_dashboard.php?msg=sent");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
