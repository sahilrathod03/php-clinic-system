<?php
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'patient') {
        header("Location: patient_dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'doctor') {
        header("Location: doctor_dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="register-page">
<div class="login-container">
    <div class="login-left">
        <img src="../img/new_acc.jpg" alt="Clinic Visual">
    </div>
    <div class="login-right">
        <img src="../img/logonobg.png" alt="Clinic Logo" class="logo">
        <h2>Create Account</h2>
        <p>Register to access the clinic portal.</p>
        <form method="POST" action="register_process.php">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <select name="role" required>
                    <style> select {
                                width: 100%;
                                padding: 10px;
                                margin: 10px 0;
                                font-size: 16px;
                                border: 1px solid #ccc;
                                border-radius: 7px;
                                display: block;
                                box-sizing: border-box;
                            }
                    </style>
                <option value="">Select Role</option>
                <option value="patient">Patient</option>
                <option value="doctor">Doctor</option>
            </select><br>
            <button type="submit">Register</button>
            <div class="links">
                <a href="../index.php">Already have an account? Login</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
