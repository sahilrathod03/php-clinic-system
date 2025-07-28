<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Mark all messages as read for this user
$conn->query("UPDATE messages SET is_read = 1 WHERE receiver_id = $user_id AND is_read = 0");

// Fetch messages
$notifications = [];
$msg_query = "
    SELECT m.*, u.username AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.receiver_id = $user_id
    ORDER BY m.sent_at DESC
";
$result = $conn->query($msg_query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// Get username for greeting
$name = "User";
$user_query = "SELECT username FROM users WHERE id = $user_id LIMIT 1";
$user_result = $conn->query($user_query);
if ($user_result && $row = $user_result->fetch_assoc()) {
    $name = $row['username'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 220px;
            background-color: #000;
            color: #fff;
            padding: 30px 20px;
        }

        .sidebar h3 {
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #fff;
            text-decoration: none;
            margin: 15px 0;
            font-size: 16px;
        }

        .main-content {
            flex: 1;
            padding: 40px;
        }

        .notification-box {
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }

        .notification-box.unread {
            border-left: 5px solid red;

        }
        body.dark .notification-box {
            background-color: #1e1e1e; /* dark background */
            color: #ffffff; /* white text for visibility */
            border: 1px solid #444;
        }

    </style>
</head>
<body>
<img src="../img/icon.svg" id="darkModeToggle" alt="Toggle Dark Mode" title="Toggle Theme">
<div class="dashboard-container">
    <div class="sidebar">
        <h3>Welcome,<br><?php echo htmlspecialchars($name); ?></h3>
        <?php if ($role === 'patient'): ?>
            <a href="patient_dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="book_appointment.php">Book Appointment</a>
        <?php elseif ($role === 'doctor'): ?>
            <a href="doctor_dashboard.php">Dashboard</a>
        <?php endif; ?>
        <a href="notifications.php">Notifications</a>
        <a href="#" onclick="openLogoutModal()">Logout</a>
    </div>

    <div class="main-content">
        <h2>Notifications</h2>
        <?php if (empty($notifications)): ?>
            <p>No notifications available.</p>
        <?php else: ?>
            <?php foreach ($notifications as $note): ?>
                <div class="notification-box">
                    <strong><?= htmlspecialchars($note['sender_name']) ?> (<?= date('d M Y h:i A', strtotime($note['sent_at'])) ?>)</strong><br>
                    <?= htmlspecialchars($note['message']) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div id="logoutModal" class="modal-overlay">
  <div class="modal-content">
    <h3>Confirm Logout</h3>
    <p>Are you sure you want to logout?</p>
    <div class="modal-buttons">
      <button onclick="confirmLogout()">Yes, Logout</button>
      <button onclick="closeLogoutModal()">Cancel</button>
    </div>
  </div>
</div>

<script>
function openLogoutModal() {
  document.getElementById("logoutModal").style.display = "flex";
}
function closeLogoutModal() {
  document.getElementById("logoutModal").style.display = "none";
}
function confirmLogout() {
  window.location.href = "logout.php";
}
document.getElementById('darkModeToggle').addEventListener('click', () => {
  document.body.classList.toggle('dark');
  localStorage.setItem("darkMode", document.body.classList.contains('dark') ? "enabled" : "disabled");
});
window.onload = () => {
  if (localStorage.getItem("darkMode") === "enabled") {
    document.body.classList.add("dark");
  }
}
</script>
</body>
</html>
