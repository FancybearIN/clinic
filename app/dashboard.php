<?php
include $_SERVER['DOCUMENT_ROOT'] . '/clinic/config/db_config.php'; 

session_start();

// Check if the user is logged in (you might want to restrict dashboard access)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location:/login"); // Redirect to the login route
    exit;
}


// Fetch statistics from the database
// Example queries (you'll need to adjust these based on your database structure)
$totalPatientsTreated = $conn->query("SELECT COUNT(*) FROM appointments WHERE status = 'done'")->fetchColumn();
$patientsUndergoingTreatment = $conn->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending' OR status = 'confirmed'")->fetchColumn();
$totalDoctors = $conn->query("SELECT COUNT(*) FROM doctors")->fetchColumn();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Clinic Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* Add custom styles here if needed */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Responsive columns */
            gap: 20px;
        }

        .stat-box {
            border: 1px solid #ccc;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Clinic Dashboard</h2>

        <div class="dashboard-stats">
            <div class="stat-box">
                <h3>Total Patients Treated</h3>
                <p><?php echo $totalPatientsTreated; ?></p>
            </div>
            <div class="stat-box">
                <h3>Patients Undergoing Treatment</h3>
                <p><?php echo $patientsUndergoingTreatment; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Doctors</h3>
                <p><?php echo $totalDoctors; ?></p>
            </div>
        </div>

        <footer class="mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Contact Us</h3>
                        <p>Your Clinic Address</p>
                        <p>Phone: Your Phone Number</p>
                        <p>Email: Your Email Address</p>
                    </div>
                    <div class="col-md-6 text-md-right"> 
                        <h3>Follow Us</h3>
                        <a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                        <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="#" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
