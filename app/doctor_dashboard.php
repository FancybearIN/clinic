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
            <a href="/" class="logo navbar-brand mt-2 mt-lg-0"> 
                <i class="fas fa-heartbeat"></i> 
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
                <!-- ... Your existing Dashboard Content ... -->
                <h2>Welcome to your Dashboard, Dr. <?php echo $_SESSION['username']; ?>!</h2>
            </div>

            <!-- Appointments Content -->
            <div class="tab-pane fade" id="appointmentsContent" role="tabpanel" aria-labelledby="appointments-tab">
                <!-- ... Your existing Appointments Content ... -->
                <h2>Manage Your Appointments</h2>
                <!-- ... Your existing Appointment Tabs ... -->

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
                                                        class="btn btn-sm btn-primary mt-2">Update
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            } else { ?>
                                <tr>
                                    <td colspan="9">No appointments found.</td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ... Your existing Latest and Previous Appointments Tabs ... -->

                </div>
            </div>

            <!-- Prescriptions Content -->
            <div class="tab-pane fade" id="prescriptionsContent" role="tabpanel"
                 aria-labelledby="prescriptions-tab">
                <h2>Manage Prescriptions</h2>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h3>Add New Prescription</h3>
                        <?php if (isset($prescriptionSuccess)) {
                            echo "<p class='text-success'>$prescriptionSuccess</p>";
                        } ?>
                        <?php if (isset($prescriptionError)) {
                            echo "<p class='text-danger'>$prescriptionError</p>";
                        } ?>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-group">
                                <label for="patient_id">Select Patient:</label>
                                <select class="form-control" id="patient_id" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                    <?php
                                    // Fetch patients and populate the dropdown
                                    $patientsSql = "SELECT id, username FROM users WHERE role = 'patient'";
                                    $patientsStmt = $conn->query($patientsSql);
                                    while ($row = $patientsStmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . $row['id'] . "'>" . $row['username'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="prescription_text">Prescription:</label>
                                <textarea class="form-control" id="prescription_text" name="prescription_text" rows="5"
                                          required></textarea>
                            </div>
                            <button type="submit" name="add_prescription" class="btn btn-primary">Add
                                Prescription
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/mdb-ui-kit@6.4.2/js/mdb.min.js"
        integrity="sha384-FIX2gN8nHSzwKGnPvimpBz2h/yat5g9vCl9x60m2iHp22vZ+LQdK6YJ4Ym6wevX/"
        crossorigin="anonymous"></script>
</body>
</html>