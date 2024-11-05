<?php
include '../config/db_config.php';
session_start();

// Check if the user is logged in and is a doctor
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'doctor') {
    header("location: /app/login.php");
    exit;
}

// Fetch doctor's information from the database
$doctorId = $_SESSION["id"]; 
$sql = "SELECT * FROM doctors WHERE id = :doctor_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':doctor_id', $doctorId);
$stmt->execute();
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    // Handle case where doctor is not found (e.g., redirect to an error page)
    die("Doctor not found."); 
}

// Handle appointment status updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $appointmentId = $_POST['appointment_id'];
    $newStatus = $_POST['status'];

    // Update appointment status in the database
    $sql = "UPDATE appointments SET status = :status WHERE id = :appointment_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $newStatus);
    $stmt->bindParam(':appointment_id', $appointmentId);

    if ($stmt->execute()) {
        $successMessage = "Appointment status updated successfully!";
    } else {
        $errorMessage = "Error updating appointment status.";
    }
}

// Fetch appointments for the logged-in doctor
$sql = "SELECT a.*, p.name AS patient_name 
        FROM appointments a
        JOIN users p ON a.patient_id = p.id 
        WHERE a.doctor_id = :doctor_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':doctor_id', $doctorId);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        /* Add custom styles here if needed */
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, Dr. <?php echo $doctor['name']; ?>!</h2>

        <h3>Your Profile</h3>
        <p><strong>ID:</strong> <?php echo $doctor['id']; ?></p>
        <p><strong>Name:</strong> <?php echo $doctor['name']; ?></p>
        <p><strong>Position:</strong> <?php echo $doctor['position']; ?></p>
        <p><strong>Email:</strong> <?php echo $doctor['email']; ?></p>

        <h3>Your Appointments</h3>
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient Name</th>
                    <th>Age</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Reason</th>
                    <th>Time Slot</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo $appointment['id']; ?></td>
                        <td><?php echo $appointment['patient_name']; ?></td>
                        <td><?php echo $appointment['age']; ?></td>
                        <td><?php echo $appointment['email']; ?></td>
                        <td><?php echo $appointment['phone']; ?></td>
                        <td><?php echo $appointment['reason']; ?></td>
                        <td><?php echo $appointment['timeslot']; ?></td>
                        <td><?php echo $appointment['status']; ?></td>
                        <td>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                <select name="status" class="form-control">
                                    <option value="pending" <?php if ($appointment['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="postponed" <?php if ($appointment['status'] == 'postponed') echo 'selected'; ?>>Postponed</option>
                                    <option value="done" <?php if ($appointment['status'] == 'done') echo 'selected'; ?>>Done</option>
                                    <option value="ignored" <?php if ($appointment['status'] == 'ignored') echo 'selected'; ?>>Ignored</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-primary mt-2">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="/app/export.php" class="btn btn-primary">Download Appointments (CSV)</a>
        <a href="/app/logout.php" class="btn btn-danger">Logout</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
