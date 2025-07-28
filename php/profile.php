<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}

include 'db_connect.php';
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM patient_details WHERE patient_id = $user_id LIMIT 1";
$result = $conn->query($query);
$patient = $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $age     = (int)$_POST['age'];
    $gender  = mysqli_real_escape_string($conn, $_POST['gender']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    if ($patient) {
        // Record exists → Update
        $update = "UPDATE patient_details 
                   SET name = '$name', age = $age, gender = '$gender', contact = '$contact', address = '$address' 
                   WHERE patient_id = $user_id";
        $conn->query($update);
    } else {
        // No record yet → Insert
        $insert = "INSERT INTO patient_details (patient_id, name, age, gender, contact, address) 
                   VALUES ($user_id, '$name', $age, '$gender', '$contact', '$address')";
        $conn->query($insert);
    }

    header("Location: profile.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: #fff;
            color: #000;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        h2 {
            font-weight: 500;
            margin-bottom: 30px;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 20px;
            font-weight: 500;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #fff;
            color: #000;
        }

        button {
            margin-top: 30px;
            width: 100%;
            padding: 14px;
            background-color: #000;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #333;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            font-size: 14px;
            color: #000;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Dark Mode Styles */
        body.dark {
            background-color: #000;
            color: #fff;
        }

        body.dark .container {
            background-color: #111;
            color: #fff;
        }

        body.dark input,
        body.dark textarea,
        body.dark select {
            background-color: #222;
            color: #fff;
            border: 1px solid #555;
        }

        body.dark .back-link {
            color: #fff;
        }

        #darkModeToggle {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 32px;
            height: 32px;
            cursor: pointer;
            z-index: 999;
            transition: transform 0.3s ease;
        }

        #darkModeToggle:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<img src="../img/icon.svg" id="darkModeToggle" alt="Toggle Dark Mode" title="Toggle Theme">

<div class="container">
    <h2>Edit My Profile</h2>
    <form method="POST">
        <label for="name">Name</label>
        <input name="name" value="<?= isset($patient) ? htmlspecialchars($patient['name']) : '' ?>" required>

        <label for="age">Age</label>
        <input type="number" name="age" value="<?= isset($patient) ? $patient['age'] : '' ?>" required>

        <label for="gender">Gender</label>
        <select name="gender" required>
            <option <?= isset($patient) && $patient['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
            <option <?= isset($patient) && $patient['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
            <option <?= isset($patient) && $patient['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
        </select>

        <label for="contact">Contact</label>
        <input name="contact" value="<?= isset($patient) ? htmlspecialchars($patient['contact']) : '' ?>" required>

        <label for="address">Address</label>
        <textarea name="address" required><?= isset($patient) ? htmlspecialchars($patient['address']) : '' ?></textarea>

        <button type="submit">Save Changes</button>
    </form>
    <a class="back-link" href="patient_dashboard.php">← Back to Dashboard</a>
</div>

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
