<?php
// Start the session to access session variables
session_start();

// Include the database configuration file
include '../config/db_config.php';

// Enable error reporting (for development purposes, disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

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

// Get the logged-in doctor's ID from the session
$doctorId = $_SESSION['id'];

// --- Fetch Appointments for the Logged-in Doctor ---

$sql = "SELECT a.*, p.username AS patient_name, p.age AS patient_age, 
               p.email AS patient_email, p.phone AS patient_phone
        FROM appointments a
        JOIN users p ON a.patient_id = p.id
        WHERE a.doctor_id = :doctor_id 
        ORDER BY a.timeslot DESC"; // Assuming you want to order by timeslot

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

// Handle Appointment Deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_appointment'])) {
    $appointmentId = $_POST['appointment_id'];

    $sql = "DELETE FROM appointments WHERE id = :appointment_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':appointment_id', $appointmentId);

    if ($stmt->execute()) {
        $successMessage = "Appointment deleted successfully!";
    } else {
        $errorMessage = "Error deleting appointment.";
    }
}

// Handle Prescription Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_prescription'])) {
    // Get the form data
    $patientId = $_POST['patient_id']; 
    $prescriptionText = $_POST['prescription_text'];

    // Basic validation (you should add more robust validation)
    if (empty($patientId) || empty($prescriptionText)) {
        $prescriptionError = "Please select a patient and enter a prescription.";
    } else {
        // Insert the prescription into the database
        $sql = "INSERT INTO prescriptions (patient_id, prescription_text, date_prescribed) 
                VALUES (:patient_id, :prescription_text, NOW())"; 
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->bindParam(':prescription_text', $prescriptionText);

        if ($stmt->execute()) {
            $prescriptionSuccess = "Prescription added successfully!";
        } else {
            $prescriptionError = "Error adding prescription.";
        }
    }
}

// --- Fetch data for dashboard statistics ---

// 1. Doctors Online (Assuming you have a way to track online status, e.g., a 'last_active' timestamp in the doctors table)
$sql = "SELECT COUNT(*) FROM doctors WHERE last_active > DATE_SUB(NOW(), INTERVAL 5 MINUTE)"; // Adjust the interval as needed
$stmt = $conn->prepare($sql);
$stmt->execute();
$doctorsOnline = $stmt->fetchColumn();

// 2. Patients Online (Similar to doctors, assuming you track patient online status)
$sql = "SELECT COUNT(*) FROM users WHERE role = 'patient' AND last_active > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
$stmt = $conn->prepare($sql);
$stmt->execute();
$patientsOnline = $stmt->fetchColumn();

// 3. Appointment Stats (You can customize these queries based on your needs)
$totalAppointments = count($appointments); // Total appointments for the logged-in doctor
$pendingAppointments = count(array_filter($appointments, function($app) { return $app['status'] == 'pending'; }));
$confirmedAppointments = count(array_filter($appointments, function($app) { return $app['status'] == 'confirmed'; }));
// ... add more stats as needed

// 4. Total Patients
$sql = "SELECT COUNT(*) FROM users WHERE role = 'patient'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$totalPatients = $stmt->fetchColumn();

// 5. Today's Appointments
$today = date('Y-m-d');
$sql = "SELECT COUNT(*) FROM appointments WHERE doctor_id = :doctor_id AND DATE(timeslot) = :today";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':doctor_id', $doctorId);
$stmt->bindParam(':today', $today);
$stmt->execute();
$todaysAppointments = $stmt->fetchColumn();

// 6. Total Cases Resolved (Assuming you have a way to track resolved cases, e.g., a 'status' field in the appointments table)
$sql = "SELECT COUNT(*) FROM appointments WHERE doctor_id = :doctor_id AND status = 'done'";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':doctor_id', $doctorId);
$stmt->execute();
$totalCasesResolved = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mdb-ui-kit@6.4.2/css/mdb.min.css"
          integrity="sha384-rVonjxPhWXc2uYVSFDqK+EBidaK+Ouo/bK49mRU2bVmYdrjQVw+wtnBJWxbC4iL+t" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Add custom styles here if needed */
        .wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .fm-box {
            width: 400px;
            padding: 40px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
    <!-- Container wrapper -->
    <div class="container-fluid">
        <!-- Toggle button -->
        <button
                data-mdb-collapse-init
                class="navbar-toggler"
                type="button"
                data-mdb-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="Toggle navigation"
        >
            <i class="fas fa-bars"></i>
        </button>

        <!-- Collapsible wrapper -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Navbar brand -->
            <a href="/" class="logo navbar-brand mt-2 mt-lg-0"> 
                <i class="fas fa-heartbeat"></i> Dr Pawan arora Clinic 
            </a>

            <!-- Left links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="#dashboardContent" data-toggle="tab"
                       data-target="#dashboardContent">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#appointmentsContent" data-toggle="tab"
                       data-target="#appointmentsContent">Appointments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#prescriptionsContent" data-toggle="tab"
                       data-target="#prescriptionsContent">Prescriptions</a>
                </li>
            </ul>
            <!-- Left links -->
        </div>
        <!-- Collapsible wrapper -->

        <!-- Right elements -->
        <div class="d-flex align-items-center">
            <!-- Icon -->
            <span class="navbar-text me-3">
                Welcome, Doctor!
            </span>
            <!-- Avatar -->
            <div class="dropdown">
                <a
                        data-mdb-dropdown-init
                        class="dropdown-toggle d-flex align-items-center hidden-arrow"
                        href="#"
                        id="navbarDropdownMenuAvatar"
                        role="button"                        aria-expanded="false">
                    <img src="https://mdbcdn.b-cdn.net/img/new/avatars/2.webp"
                            class="rounded-circle"
                            height="25"
                            alt="Black and White Portrait of a Man"
                            loading="lazy"
                    />
                </a>
                <ul
                        class="dropdown-menu dropdown-menu-end"
                        aria-labelledby="navbarDropdownMenuAvatar"
                >
                    <li>
                        <a class="dropdown-item" href="#">My profile</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">Settings</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Right elements -->
    </div>
    <!-- Container wrapper -->
</nav>
<!-- Navbar -->

<!-- Main Content Area -->
<main class="main-content">
    <div class="container-fluid">
        <div class="tab-content" id="myTabContent">
            <!-- Dashboard Content -->
            <div class="tab-pane fade show active" id="dashboardContent" role="tabpanel"
                 aria-labelledby="dashboard-tab">
                <h2>Welcome to your Dashboard, Dr. <?php echo $_SESSION['username']; ?>!</h2>

                <!-- Dashboard Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Doctors Online</h5>
                                <p class="card-text"><?php echo $doctorsOnline; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Patients Online</h5>
                                <p class="card-text"><?php echo $patientsOnline; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Total Patients</h5>
                                <p class="card-text"><?php echo $totalPatients; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Today's Appointments</h5>
                                <p class="card-text"><?php echo $todaysAppointments; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Appointment Stats</h5>
                                <canvas id="appointmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Cases Resolved</h5>
                                <p class="card-text"><?php echo $totalCasesResolved; ?></p>
                                <!-- You can add a progress bar or other visualization here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments Content -->
            <div class="tab-pane fade" id="appointmentsContent" role="tabpanel" aria-labelledby="appointments-tab">
                <h2>Manage Your Appointments</h2>

                <!-- Appointment Tab Content -->
                <div class="tab-content" id="appointmentTabContent">
                    <!-- All Appointments Tab -->
                    <div class="tab-pane fade show active" id="allAppointments" role="tabpanel"
                         aria-labelledby="allAppointments-tab">
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
                            <?php
                            // Check if $appointments is an array and not empty
                            if (is_array($appointments) && !empty($appointments)) {
                                foreach ($appointments as $appointment): ?>
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
                                            <form method="post"
                                                  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                                <input type="hidden" name="appointment_id"
                                                       value="<?php echo $appointment['id']; ?>">
                                                <select name="status" class="form-control">
                                                    <option value="pending" <?php if ($appointment['status'] == 'pending') echo 'selected'; ?>>
                                                        Pending
                                                    </option>
                                                    <option value="confirmed" <?php if ($appointment['status'] == 'confirmed') echo 'selected'; ?>>
                                                        Confirmed
                                                    </option>
                                                    <option value="postponed" <?php if ($appointment['status'] == 'postponed') echo 'selected'; ?>>
                                                        Postponed
                                                    </option>
                                                    <option value="done" <?php if ($appointment['status'] == 'done') echo 'selected'; ?>>
                                                        Done
                                                    </option>
                                                    <option value="ignored" <?php if ($appointment['status'] == 'ignored') echo 'selected'; ?>>
                                                        Ignored
                                                    </option>
                                                </select>
                                                <button type="submit" name="update_status"
                                                        class="btn btn-sm btn-primary
