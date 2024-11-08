<?php
// gandhi edits

include '../config/db_config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Fetch patient's information from the database
$patientId = $_SESSION["id"];
$sql = "SELECT * FROM users WHERE id = :patient_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':patient_id', $patientId);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found.");
}

// Fetch patient's appointments (you'll need to write this query)
// Example:
$sql = "SELECT a.*, d.name AS doctor_name 
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.patient_id = :patient_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':patient_id', $patientId);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch patient's prescriptions (you'll need to write this query)
// Example:
$sql = "SELECT * FROM prescriptions WHERE patient_id = :patient_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':patient_id', $patientId);
$stmt->execute();
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        /* Add custom styles here if needed */
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $patient['username']; ?>!</h2>

        <h3>Your Profile</h3>
        <p><strong>ID:</strong> <?php echo $patient['id']; ?></p>
        <p><strong>Username:</strong> <?php echo $patient['username']; ?></p>
        <p><strong>Email:</strong> <?php echo $patient['email']; ?></p>

        <!-- Add the code snippet here -->
        <h3>Your Appointments</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Doctor</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo date('Y-m-d', strtotime($appointment['created_at'])); ?></td>
                        <td><?php echo date('H:i', strtotime($appointment['created_at'])); ?></td>
                        <td><?php echo $appointment['doctor_name']; ?></td> 
                        <td><?php echo $appointment['status']; ?></td>
                        <td>
                            <a href="/appointment/details/<?php echo $appointment['id']; ?>" class="btn btn-sm btn-info">View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Prescriptions</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Medication</th>
                    <th>Dosage</th>
                    <th>Date Prescribed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prescriptions as $prescription): ?>
                    <tr>
                        <td><?php echo $prescription['medication']; ?></td>
                        <td><?php echo $prescription['dosage']; ?></td>
                        <td><?php echo $prescription['date_prescribed']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- End of code snippet -->

        <h3>Patient Details</h3>
        <p>Add patient details here (e.g., address, medical history, etc.)</p>

        <h3>Payment Options</h3>
        <p>Add payment options here (e.g., credit card, insurance, etc.)</p>

        <h3>Bills</h3>
        <p>Add bill history here.</p>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
