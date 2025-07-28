<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $db_password, $role);
        $stmt->fetch();

        // Compare plain-text passwords (for demo only; use hashing in production)
        if ($password === $db_password) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;

            if ($role === 'patient') {
                header("Location: patient_dashboard.php");
                exit();
            } elseif ($role === 'doctor') {
                header("Location: doctor_dashboard.php");
                exit();
            }
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>
