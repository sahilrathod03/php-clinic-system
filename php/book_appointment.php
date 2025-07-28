<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}
include 'db_connect.php';
$user_id = $_SESSION['user_id'];
$doctor_query = "SELECT id, username FROM users WHERE role = 'doctor'";
$doctors = $conn->query($doctor_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .blurred {
            filter: blur(5px);
            pointer-events: none;
        }

        .modal {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            display: flex; justify-content: center; align-items: center;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        .modal-content h2 {
            margin-top: 0;
        }

        .modal-content form input, 
        .modal-content form select, 
        .modal-content form textarea {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .modal-content button {
            width: 100%;
            padding: 12px;
            background: #000;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 20px;
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
<img src="../img/icon.svg" id="darkModeToggle" alt="Toggle Dark Mode" title="Toggle Theme">
    <?php if (isset($_GET['message'])): ?>
        <div id="toast" class="toast"><?= htmlspecialchars($_GET['message']) ?></div>
    <?php endif; ?>
    <script>
        window.addEventListener("DOMContentLoaded", () => {
        const toast = document.getElementById("toast");
            if (toast) {
            setTimeout(() => {
            toast.style.display = "none";
             }, 5000); // 5 seconds
         }
     });
    </script>

    <div id="main-content">
        <div class="dashboard-container">
            <div class="sidebar">
                <h3>Welcome,<br>Patient</h3>
                <a href="patient_dashboard.php">Dashboard</a>
                <a href="#" onclick="openModal()">Book Appointment</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
            <div class="main-content">
                <h2>Book Appointment</h2>
                <p>Click the below button to book an appointment.</p>
                <button onclick="openModal()"> Book Appointment</button>
            </div>
        </div>
    </div>

    <div id="appointmentModal" class="modal" style="display: none;">
        <button class="close-btn" onclick="closeModal()">×</button>
        <div class="modal-content">
            <h2>New Appointment</h2>
            <form action="submit_appointment.php" method="POST">
                <label>Choose Doctor</label>
                <select name="doctor_id" required>
                    <option value="">Select a doctor</option>
                    <?php while($doc = $doctors->fetch_assoc()): ?>
                        <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['username']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label>Appointment Date</label>
                <input type="date" name="appointment_date" required>

                <label>Appointment Time</label>
                <input type="time" name="appointment_time" required>

                <label>Issue Description</label>
                <textarea name="issue_description" rows="3" required></textarea>

                <button type="submit">Confirm Appointment</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('appointmentModal').style.display = 'flex';
            document.getElementById('main-content').classList.add('blurred');
        }

        function closeModal() {
            document.getElementById('appointmentModal').style.display = 'none';
            document.getElementById('main-content').classList.remove('blurred');
        }
    </script>
    <script>
        // Load dark mode state from localStorage
        document.addEventListener('DOMContentLoaded', () => {
        const isDark = localStorage.getItem('darkMode') === 'true';
            if (isDark) {
                document.body.classList.add('dark');
            }
            });

        document.getElementById('darkModeToggle').addEventListener('click', () => {
        document.body.classList.toggle('dark');
        const isDark = document.body.classList.contains('dark');
        localStorage.setItem('darkMode', isDark);
    });
    </script>

</body>
</html>
