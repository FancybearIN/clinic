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
            <a class="navbar-brand mt-2 mt-lg-0" href="#">
                <img
                        src="https://mdbcdn.b-cdn.net/img/logo/mdb-transaprent-noshadows.webp"
                        height="15"
                        alt="MDB Logo"
                        loading="lazy"
                />
            </a>
            <!-- Left links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="#">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Team</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Projects</a>
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
            <a class="text-reset me-3" href="#">
                <i class="fas fa-user"></i>
            </a>

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
                        <a class="dropdown-item" href="#">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Right elements -->
    </div>
    <!-- Container wrapper -->
</nav>
<!-- Navbar -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/mdb-ui-kit@6.4.2/js/mdb.min.js"
        integrity="sha384-FIX2gN8nHSzwKGnPvimpBz2h/yat5g9vCl9x60m2iHp22vZ+LQdK6YJ4Ym6wevX/"
        crossorigin="anonymous"></script>
</body>
</html>



