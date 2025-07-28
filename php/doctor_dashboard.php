<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get doctor name
$name = "Doctor";
$query = "SELECT username FROM users WHERE id = $user_id LIMIT 1";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $name = $row['username'];
}

// Fetch appointments with patient name
$appointments = [];
$appt_query = "
    SELECT a.*, p.name AS patient_name
    FROM appointments a
    JOIN patient_details p ON a.patient_id = p.patient_id
    WHERE a.doctor_id = $user_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";
$appt_result = $conn->query($appt_query);
if ($appt_result && $appt_result->num_rows > 0) {
    while ($row = $appt_result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<!-- Submit Prescription Modal -->
<div id="prescriptionModal" class="modal-overlay">
  <div class="modal-content">
    <h3>Submit Prescription</h3>
    <form method="POST" action="submit_prescription.php">
      <label>Patient ID:</label>
      <input type="number" name="patient_id" required>

      <label>Appointment ID:</label>
      <input type="number" name="appointment_id" required>

      <label>Medicine:</label>
      <input type="text" name="medicine" required>

      <label>Dosage:</label>
      <input type="text" name="dosage" required>

      <label>Instructions:</label>
      <textarea name="instructions" required></textarea>

      <div class="button-group">
        <button type="submit" class="btn">Submit</button>
        <button type="button" onclick="closePrescriptionModal()" class="btn btn-cancel">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Upload Lab Report Modal -->
<div id="labReportModal" class="modal-overlay">
  <div class="modal-content">
    <h3>Upload Lab Report</h3>
    <form method="POST" action="upload_lab_report.php">
      <label>Patient ID:</label>
      <input type="number" name="patient_id" required>

      <label>Report Title:</label>
      <input type="text" name="report_title" required>

      <label>Findings:</label>
      <textarea name="findings" required></textarea>

      <div class="button-group">
        <button type="submit" class="btn">Upload</button>
        <button type="button" onclick="closeLabReportModal()" class="btn btn-cancel">Cancel</button>
      </div>
    </form>
  </div>
</div>

<img src="../img/icon.svg" id="darkModeToggle" alt="Toggle Dark Mode" title="Toggle Theme">
<div class="dashboard-container">
    <div class="sidebar">
        <h3>Welcome,<br><?php echo htmlspecialchars($name); ?></h3>
        <a href="#">Appointments</a>
        <a href="javascript:void(0)" onclick="openPrescriptionModal()">Submit Prescription</a>
        <a href="javascript:void(0)" onclick="openLabReportModal()">Upload Lab Report</a>
        <a href="notification.php">Notifications</a>
        <a href="javascript:void(0)" onclick="openMessageModal()">Send Message</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h2>Appointments</h2>
        <?php if (empty($appointments)): ?>
            <p>No appointments found.</p>
        <?php else: ?>
            <?php foreach ($appointments as $appt): ?>
                <div class="appointment-card">
                    <strong>Date:</strong> <?= $appt['appointment_date'] ?><br>
                    <strong>Time:</strong> <?= $appt['appointment_time'] ?><br>
                    <strong>Patient:</strong> <?= htmlspecialchars($appt['patient_name']) ?><br>
                    <strong>Status:</strong> <?= $appt['status'] ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<div id="messageModal" class="modal-overlay">
  <div class="modal-content">
    <h3>Send Message to Patient</h3>
    <form method="POST" action="send_message_doc.php">
      <label for="receiver_id">Select Patient:</label>
      <select name="receiver_id" required>
        <?php
          $patients = $conn->query("SELECT id, username FROM users WHERE role='patient'");
          while ($pat = $patients->fetch_assoc()) {
              echo "<option value='{$pat['id']}'>{$pat['username']}</option>";
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
</div>

<script>
document.getElementById('darkModeToggle').addEventListener('click', () => {
    document.body.classList.toggle('dark');
    localStorage.setItem('darkMode', document.body.classList.contains('dark'));
});
window.onload = () => {
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark');
    }
};
</script>
<script>
function openMessageModal() {
  document.getElementById("messageModal").style.display = "flex";
}
function closeMessageModal() {
  document.getElementById("messageModal").style.display = "none";
}
</script>
<script>
function openPrescriptionModal() {
  document.getElementById("prescriptionModal").style.display = "flex";
}
function closePrescriptionModal() {
  document.getElementById("prescriptionModal").style.display = "none";
}

function openLabReportModal() {
  document.getElementById("labReportModal").style.display = "flex";
}
function closeLabReportModal() {
  document.getElementById("labReportModal").style.display = "none";
}
</script>
</body>
</html>