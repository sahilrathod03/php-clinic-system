<?php
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'patient') {
        header("Location: php/patient_dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'doctor') {
        header("Location: php/doctor_dashboard.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clinic Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
<div class="login-container">
    <div class="login-left">
        <img src="img/login-illustration.jpg" alt="Clinic Visual">
    </div>
    <div class="login-right">
        <img src="img/logonobg.png" alt="Clinic Logo" class="logo">
        <h2>Login</h2>
        <p>Welcome to our clinic portal.</p>
        <form method="POST" action="php/login.php">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <div class="links">
                <a href="php/forget_password.php">Forgot Password?</a>
                <a href="php/register.php">Create Account</a>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</div>
</body>
</html>
