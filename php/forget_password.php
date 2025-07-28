<?php
session_start();
include 'db_connect.php';

$step = isset($_SESSION['step']) ? $_SESSION['step'] : 'username';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
        $res = $conn->query("SELECT * FROM users WHERE username = '$username'");
        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
            $uid = $user['id'];
            $role = $user['role'];
            $table = $role === 'patient' ? 'patient_details' : 'doctor_details';
            $crow = $conn->query("SELECT contact FROM $table WHERE $role" . "_id = $uid")->fetch_assoc();
            if ($crow) {
                $contact = $crow['contact'];
                $now = time();
                if (!isset($_SESSION['otp_timestamp']) || $now - $_SESSION['otp_timestamp'] > 60) {
                    $_SESSION['otp'] = rand(100000, 999999);
                    $_SESSION['otp_timestamp'] = $now;
                    $_SESSION['otp_expire'] = $now + 300;
                    $_SESSION['username'] = $username;
                    $_SESSION['contact'] = $contact;
                    $_SESSION['step'] = 'otp';
                    $step = 'otp';
                    $message = "OTP sent to contact: " . $_SESSION['contact'] . "<br><strong>OTP (testing only): " . $_SESSION['otp'] . "</strong>";
                } else {
                    $wait = 60 - ($now - $_SESSION['otp_timestamp']);
                    $message = "Please wait $wait seconds before resending OTP.";
                }
            } else {
                $message = "Contact not found for user.";
            }
        } else {
            $message = "Username not found.";
        }
    } elseif (isset($_POST['otp'])) {
        $entered_otp = trim($_POST['otp']);
        if ($entered_otp == $_SESSION['otp'] && time() <= $_SESSION['otp_expire']) {
            $_SESSION['step'] = 'reset';
            $step = 'reset';
        } else {
            $message = "Invalid or expired OTP.";
        }
    } elseif (isset($_POST['new_password'], $_POST['confirm_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $message = "Passwords do not match.";
            $step = 'reset';
        } else {
            $username = $_SESSION['username'];
            $newpass = trim($_POST['new_password']);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $newpass, $username);
            if ($stmt->execute()) {
                session_unset();
                session_destroy();
                echo "<script>alert('Password changed successfully!'); window.location.href='../index.php';</script>";
                exit();
            } else {
                $message = "Password update failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forget Password</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .overlay {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.4);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-box {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            width: 360px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
        .modal-box h3 {
            margin-bottom: 20px;
        }
        .modal-box input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            font-size: 15px;
        }
        .modal-box button {
            margin-top: 15px;
            width: 100%;
            padding: 10px;
            background-color: #000;
            color: white;
            border: none;
            font-weight: bold;
        }
        .message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="reset-page">
<div class="login-container">
    <div class="login-left">
        <img src="../img/forget_pass.jpg" alt="Visual">
    </div>
    <div class="login-right">
        <h2>Reset Password</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Enter Username" required>
            <button type="submit">Send OTP</button>
            <div class="links"><a href="../index.php">Back to Login</a></div>
        </form>
    </div>
</div>

<?php if ($step !== 'username'): ?>
<div class="overlay">
    <div class="modal-box">
        <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>

        <?php if ($step === 'otp'): ?>
            <h3>Enter OTP sent to <?= htmlspecialchars($_SESSION['contact']) ?></h3>
            <form method="POST">
                <input type="text" name="otp" placeholder="Enter OTP" required>
                <button type="submit">Verify OTP</button>
            </form>

        <?php elseif ($step === 'reset'): ?>
            <h3>Set New Password</h3>
            <form method="POST">
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Re-enter Password" required>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
</body>
</html>

