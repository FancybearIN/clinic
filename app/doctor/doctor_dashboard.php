<?php
// Start the session to access session variables
session_start();

// Include the database configuration file
include '../../config/db_config.php';

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

// Fetch Appointments (All, Latest, Previous)
$sql = "SELECT a.*, p.full_name AS patient_name, p.age AS patient_age, p.email AS patient_email, p.phone AS patient_phone 
        FROM appointments a 
        JOIN users p ON a.patient_id = p.id 
        WHERE a.doctor_id = :doctor_id 
        ORDER BY a.timeslot DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':doctor_id', $doctorId); // Bind the doctor ID parameter
$stmt->execute(); // Execute the query
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all appointments

// Handle Appointment Status Updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    // Get appointment ID and new status from the POST request
    $appointmentId = $_POST['appointment_id'];
    $newStatus = $_POST['status'];

    // Prepare SQL statement to update appointment status
    $sql = "UPDATE appointments SET status = :status WHERE id = :appointment_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $newStatus); // Bind the new status
    $stmt->bindParam(':appointment_id', $appointmentId); // Bind the appointment ID

    // Execute the update query and check for success
    if ($stmt->execute()) {
        $successMessage = "Appointment status updated successfully!"; // Success message
    } else {
        $errorMessage = "Error updating appointment status."; // Error message
    }
}

// You can now use $appointments variable in your HTML to display the information

?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard & Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css"> 
    <style>
        /* Responsive Styles (using CSS Grid for layout) */
        .dashboard-container {
            display: grid;
            grid-template-columns: 1fr; /* Single column on smaller screens */
            gap: 20px;
            padding: 20px;
        }

        @media (min-width: 768px) { 
            .dashboard-container {
                grid-template-columns: 250px 1fr; /* Sidebar and main content */
            }
        }

        .sidebar {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }

        .main-content {
            /* Style the main content area */
        }

        /* ... other styles ... */
    </style>
</head>
<body>

    <!-- Header Section -->
    <header class="header"> 
        <a href="/" class="logo"> <i class="fas fa-heartbeat"></i> Dr Pawan arora Clinic </a>
        <nav class="navbar">
            <ul>
                <li><a href="#home">home</a></li>
                <li><a href="#about">about</a></li>
                <li><a href="#services">services</a></li>
                <li><a href="#doctors">doctors</a></li>
                <li><a href="#book">book</a></li>
                <li><a href="#review">review</a></li>
                <li><a href="#blogs">blogs</a></li>
            </ul>
        </nav>

        <div id="menu-btn" class="fas fa-bars"></div>
    </header>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="/doctor/doctor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-file-invoice"></i> Bills</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-prescription"></i> Prescriptions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/doctor/doctor_profile.php"><i class="fas fa-user"></i> Profile</a>
                </li>
                <li class="nav-item dropdown"> 
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> <?php echo $doctor['name']; ?> 
                    </a>
                    <div class="dropdown-menu" aria-labelledby="profileDropdown">
                        <a class="dropdown-item" href="/app/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="container-fluid">
                <!-- Doctor Profile Section -->
                <div class="row">
                    <div class="col-12">
                        <h2>Welcome, Dr. <?php echo $doctor['name']; ?>!</h2>

                        <h3>Your Profile</h3>
                        <p><strong>ID:</strong> <?php echo $doctor['id']; ?></p>
                        <p><strong>Name:</strong> <?php echo $doctor['name']; ?></p>
                        <p><strong>Position:</strong> <?php echo $doctor['position']; ?></p>
                        <p><strong>Email:</strong> <?php echo $doctor['email']; ?></p>
                    </div>
                </div>

                <!-- Appointments Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h2>Appointments</h2>

                        <!-- Appointment Tabs -->
                        <ul class="nav nav-tabs" id="appointmentTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="allAppointments-tab" data-toggle="tab" href="#allAppointments" role="tab" aria-controls="allAppointments" aria-selected="true">All</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="latestAppointments-tab" data-toggle="tab" href="#latestAppointments" role="tab" aria-controls="latestAppointments" aria-selected="false">Latest</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="previousAppointments-tab" data-toggle="tab" href="#previousAppointments" role="tab" aria-controls="previousAppointments" aria-selected="false">Previous</a>
                            </li>
                        </ul>

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

                            <!-- Latest Appointments Tab -->
                            <div class="tab-pane fade" id="latestAppointments" role="tabpanel" aria-labelledby="latestAppointments-tab">
                                <p>Content for latest appointments will be displayed here.</p>
                            </div>

                            <!-- Previous Appointments Tab -->
                            <div class="tab-pane fade" id="previousAppointments" role="tabpanel" aria-labelledby="previousAppointments-tab">
                                <p>Content for previous appointments will be displayed here.</p>
                            </div>
                        </div> 
                    </div>
                </div> 

                <!-- Add New Doctor Section (You can move this to a separate file/modal) -->
                <div class="row mt-4"> 
                    <div class="col-12">
                        <h3>Add New Doctor</h3>
                        <!-- Add New Doctor Form -->
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            </form>
                    </div>
                </div>

                <!-- Logout Button -->
                <div class="row mt-4">
                    <div class="col-12">
                        <a href="/app/logout.php" class="btn btn-danger">Logout</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
