<?php
// Start the session to access session variables
session_start();

// Include the database configuration file
include '../config/db_config.php';

// Clear browser cache to prevent caching of sensitive data
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// Set timeout duration (e.g., 30 minutes)
$timeout_duration = 1800; // 30 minutes in seconds

// Check if the user is logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Check if the last activity time is set
    if (isset($_SESSION['last_activity'])) {
        // Calculate the session's lifetime
        $session_life = time() - $_SESSION['last_activity'];
        
        // If the session has expired, log out the user
        if ($session_life > $timeout_duration) {
            session_unset(); // Unset session variables
            session_destroy(); // Destroy the session
            header("location: /app/login.php"); // Redirect to login
            exit;
        }
    }
    // Update last activity time
    $_SESSION['last_activity'] = time();
} else {
    // Redirect to login if the user is not logged in
    header("location: /app/login.php");
    exit;
}

// Now that you know the user is logged in, check the role:
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    // Redirect to login if the user is not a doctor
    header("location: /app/login.php");
    exit;
}

// Assuming $doctorId is defined somewhere in your code
$doctorId = $_SESSION['id']; // Example: assuming the doctor ID is stored in the session

// --- Doctor Assignment Logic (Removed - assigning based on time slot) ---
// Removed the logic for 6 doctors and max patients per doctor

// --- Fetch Appointments (All, Latest, Previous) ---

// Fetch all appointments for the logged-in doctor
$sql = "SELECT a.*, p.username AS patient_name, p.age AS patient_age, p.email AS patient_email, p.phone AS patient_phone 
        FROM appointments a 
        JOIN users p ON a.patient_id = p.id 
        WHERE a.doctor_id = :doctor_id 
        ORDER BY a.timeslot DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':doctor_id', $doctorId);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Appointment Status Updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $appointmentId = $_POST['appointment_id'];
    $newStatus = $_POST['status'];

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

?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard & Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css"> 
    <style>
        /* ... Your existing styles ... */
    </style>
</head>
<body>

    <!-- ... Your existing HTML (header, sidebar) ... -->

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="container-fluid">
                <!-- ... Your existing Doctor Profile Section ... -->

                <!-- Appointments Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h2>Appointments</h2>

                        <!-- ... Your existing Appointment Tabs ... -->

                        <!-- Appointment Tab Content -->
                        <div class="tab-content" id="appointmentTabContent">
                            <!-- All Appointments Tab -->
                            <div class="tab-pane fade show active" id="allAppointments" role="tabpanel" aria-labelledby="allAppointments-tab">
                                <table class="table table-striped">
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
                                                <td><?php echo $appointment['patient_age']; ?></td> 
                                                <td><?php echo $appointment['patient_email']; ?></td> 
                                                <td><?php echo $appointment['patient_phone']; ?></td> 
                                                <td><?php echo $appointment['reason']; ?></td>
                                                <td><?php echo $appointment['timeslot']; ?></td>
                                                <td><?php echo $appointment['status']; ?></td>
                                                <td>
                                                    <!-- Appointment Status Update Form -->
                                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                        <select name="status" class="form-control">
                                                            <option value="pending" <?php if ($appointment['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                                            <option value="confirmed" <?php if ($appointment['status'] == 'confirmed') echo 'selected'; ?>>Confirmed</option>
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
                            </div>

                            <!-- ... Your existing Latest and Previous Appointments Tabs ... -->

                        </div> 
                    </div>
                </div> 

                <!-- ... Your existing Add New Doctor Section and Logout Button ... -->

            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
