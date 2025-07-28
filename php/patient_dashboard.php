<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}

include 'db_connect.php';
$user_id = $_SESSION['user_id'];

// Check if feedback is submitted
if (isset($_POST['submit_feedback']) && !empty($_POST['feedback'])) {
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    $insert = "INSERT INTO feedback (patient_id, message) VALUES ('$user_id', '$feedback')";
    mysqli_query($conn, $insert);
}
// Get patient name
$name = "Patient";
$query = "SELECT username FROM users WHERE id = $user_id LIMIT 1";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $name = $row['username'];
}

// Get unread notification count
$notif_count = 0;
$notif_result = $conn->query("SELECT COUNT(*) AS unread FROM messages WHERE receiver_id = $user_id AND is_read = 0");
if ($notif_result && $row = $notif_result->fetch_assoc()) {
    $notif_count = $row['unread'];
}

// Fetch appointments with doctor names
$appointments = [];
$appt_query = "
    SELECT a.*, u.username AS doctor_name
    FROM appointments a
    JOIN users u ON a.doctor_id = u.id
    WHERE a.patient_id = $user_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";
$appt_result = $conn->query($appt_query);
if ($appt_result && $appt_result->num_rows > 0) {
    while ($row = $appt_result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

// Separate appointments into upcoming and completed
$upcoming = [];
$completed = [];
date_default_timezone_set("Asia/Kolkata");
$now = date('Y-m-d H:i:s');

foreach ($appointments as $appt) {
    $appt_datetime = $appt['appointment_date'] . ' ' . $appt['appointment_time'];
    if ($appt_datetime >= $now) {
        $upcoming[] = $appt;
    } else {
        $completed[] = $appt;
    }
}

// Lab Reports
$lab_reports = [];
$lab_result = $conn->query("SELECT report_title, findings FROM lab_reports WHERE patient_id = $user_id");
if ($lab_result && $lab_result->num_rows > 0) {
    while ($row = $lab_result->fetch_assoc()) {
        $lab_reports[] = $row['report_title'] . ' - ' . $row['findings'];
    }
}

// Prescriptions
$prescriptions = [];
$presc_result = $conn->query("SELECT medicine FROM prescriptions WHERE patient_id = $user_id");
if ($presc_result && $presc_result->num_rows > 0) {
    while ($row = $presc_result->fetch_assoc()) {
        $prescriptions[] = $row['medicine'];
    }
}

// Last visit
$last_visit_date = null;
foreach ($completed as $appt) {
    if (!$last_visit_date) {
        $last_visit_date = date('d M Y', strtotime($appt['appointment_date']));
        break;
    }
}
// Doctor Notes
$doctor_notes = [];
$notes_result = $conn->query("SELECT note FROM doctor_notes WHERE patient_id = $user_id ORDER BY noted_at DESC LIMIT 5");
if ($notes_result && $notes_result->num_rows > 0) {
    while ($row = $notes_result->fetch_assoc()) {
        $doctor_notes[] = $row['note'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dashboard-container {
            display: flex;
            height: 100vh;
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

        .appointment-card {
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .grid-cards {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            margin-bottom: 20px;
        }

        .card-box {
            flex: 1;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        h2 {
            margin-top: 30px;
        }
    </style>
</head>
<body>
<img src="../img/icon.svg" id="darkModeToggle" alt="Toggle Dark Mode" title="Toggle Theme">
<div class="dashboard-container">
   <div class="sidebar">
    <h3>Welcome,<br><?php echo htmlspecialchars($name); ?></h3>
    <a href="#">My Appointments</a>
    <a href="profile.php">Profile</a>
    <a href="book_appointment.php">Book Appointment</a>
    <a href="javascript:void(0)" onclick="openMessageModal()">Messages</a>
    <a href="notification.php"> Notifications<?= $notif_count > 0 ? "<span style='color:red;'>($notif_count)</span>" : "" ?></a>
    <a href="javascript:void(0)" onclick="openFeedback()">Feedback</a>
    <a href="#" onclick="openLogoutModal()">Logout</a>
</div>

    <div class="main-content">
        <h2>Upcoming Appointments</h2>
        <?php if (empty($upcoming)): ?>
            <p>No upcoming appointments.</p>
        <?php else: ?>
            <?php foreach ($upcoming as $appt): ?>
                <div class="appointment-card">
                    <strong>Date:</strong> <?= $appt['appointment_date'] ?><br>
                    <strong>Time:</strong> <?= $appt['appointment_time'] ?><br>
                    <strong>Doctor:</strong> <?= htmlspecialchars($appt['doctor_name']) ?><br>
                    <strong>Status:</strong> <?= $appt['status'] ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2>Completed Appointments</h2>
        <?php if (empty($completed)): ?>
            <p>No completed appointments.</p>
        <?php else: ?>
            <?php foreach ($completed as $appt): ?>
                <div class="appointment-card">
                    <strong>Date:</strong> <?= $appt['appointment_date'] ?><br>
                    <strong>Time:</strong> <?= $appt['appointment_time'] ?><br>
                    <strong>Doctor:</strong> <?= htmlspecialchars($appt['doctor_name']) ?><br>
                    <strong>Status:</strong> <?= $appt['status'] ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="grid-cards">
            <div class="card-box">
                <strong>Lab Reports</strong><br>
                <ul>
                    <?php foreach ($lab_reports as $report): ?>
                        <li><?= htmlspecialchars($report) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card-box">
                <strong>Prescriptions</strong><br>
                <ul>
                    <?php foreach ($prescriptions as $presc): ?>
                        <li><?= htmlspecialchars($presc) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card-box">
                <strong>Doctor Notes</strong><br>
                Recent visit: <?= $last_visit_date ?? 'N/A' ?><br>
                <ul>
                    <?php foreach ($doctor_notes as $note): ?>
                        <li><?= htmlspecialchars($note) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="card-box">
            <strong>Health Tips</strong><br>
            Drink water regularly and walk at least 30 minutes daily.
        </div>
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
<!-- Need Consult Button -->
<div id="consultBtn">Need Consult?</div>

<!-- AI Chat Popup -->
<div id="consultOverlay">
  <div class="consult-popup">
    <span class="close-btn" onclick="closeConsult()">×</span>
    <h3>AI Health Assistant</h3>
    <div id="chatBox">
      <div class="chat-message bot">Hi! Describe your symptoms and I’ll try to assist you.</div>
    </div>
    <form onsubmit="return sendMessage();">
      <input type="text" id="userMessage" placeholder="Enter your symptoms..." required>
      <button type="submit">Send</button>
    </form>
  </div>
</div>
<script>
function openConsult() {
    document.getElementById("consultOverlay").style.display = "flex";
}

function closeConsult() {
    document.getElementById("consultOverlay").style.display = "none";
}

// Trigger open
document.getElementById("consultBtn").addEventListener("click", openConsult);

// Simulated AI Response
function sendMessage() {
    const input = document.getElementById("userMessage");
    const msg = input.value.trim();
    if (!msg) return false;

    const chatBox = document.getElementById("chatBox");

    // User message
    const userDiv = document.createElement("div");
    userDiv.className = "chat-message user";
    userDiv.textContent = msg;
    chatBox.appendChild(userDiv);

    // Simulated bot reply
    const botDiv = document.createElement("div");
    botDiv.className = "chat-message bot";

    const response = simulateAIResponse(msg);
    setTimeout(() => {
        botDiv.textContent = response;
        chatBox.appendChild(botDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    }, 500);

    input.value = "";
    return false;
}

function simulateAIResponse(msg) {
    msg = msg.toLowerCase();
    if (msg.includes("fever"))
        return "It may be a viral infection. Stay hydrated and rest. See a doctor if symptoms worsen.";
    if (msg.includes("headache"))
        return "It could be due to stress or dehydration. Try drinking water and resting.";
    if (msg.includes("stomach"))
        return "It might be indigestion. Eat light and avoid oily food. See a doctor if pain persists.";
    return "I'm not sure. It’s best to consult a doctor directly for accurate diagnosis.";
}
</script>
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

<div id="feedbackModal" class="modal-overlay">
  <div class="modal-content">
    <h3>Give Feedback</h3>
    <form method="POST">
      <textarea name="feedback" placeholder="Your feedback..." required></textarea>
      <div class="button-group">
        <button type="submit" name="submit_feedback" class="btn">Submit</button>
        <button type="button" onclick="closeFeedback()" class="btn btn-cancel">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openFeedback() {
  document.getElementById("feedbackModal").style.display = "flex";
}

function closeFeedback() {
  document.getElementById("feedbackModal").style.display = "none";
}
</script>
<div id="messageModal" class="modal-overlay">
  <div class="modal-content">
    <h3>Send Message</h3>
    <form method="POST" action="send_message.php">
      <label for="doctor">Select Doctor:</label>
      <select name="receiver_id" required>
        <?php
          $doc_result = $conn->query("SELECT id, username FROM users WHERE role='doctor'");
          while ($doc = $doc_result->fetch_assoc()) {
            echo "<option value='{$doc['id']}'>{$doc['username']}</option>";
          }
        ?>
      </select>
      <textarea name="message" placeholder="Type your message..." required></textarea>
      <div class="button-group">
        <button type="submit" class="btn">Send</button>
        <button type="button" onclick="closeMessageModal()" class="btn btn-cancel">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
    function openMessageModal() {
  document.getElementById("messageModal").style.display = "flex";
}
function closeMessageModal() {
  document.getElementById("messageModal").style.display = "none";
}

</script>
</body>
</html>
