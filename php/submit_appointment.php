<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}

include 'db_connect.php';

$patient_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor_id = intval($_POST['doctor_id']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $issue_description = mysqli_real_escape_string($conn, $_POST['issue_description']);

    $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, issue_description, status)
              VALUES ($patient_id, $doctor_id, '$appointment_date', '$appointment_time', '$issue_description', 'Scheduled')";

    if ($conn->query($query)) {
        header("Location: book_appointment.php?message=Appointment+Booked+Successfully");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
