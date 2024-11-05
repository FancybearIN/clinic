<?php
include './config/db_config.php'; // Include database configuration
session_start();

// Define routes and their corresponding content files
$routes = [
    '/' => 'app/index.php', // Home page
    '/dashboard' => 'app/dashboard.php', // Home page
    '/about' => 'app/about.php', // About page
    '/services' => 'app/services.php', // Services page
    '/contact' => 'app/contact.php', // Contact page
    '/login' => 'app/login.php', // Login page
    '/registor' => 'app/registor.php', // Registration page
    '/doctor/profile' => 'app/doctor/doctor_profile.php', // Doctor profile page
    '/patient/profile' => 'app/patient/profile.php', // Patient profile page
    '/patient/records/(.*)' => 'app/patient/record.php', // Patient records page
    '/appointment/details/(.*)' => 'app/appointment_details.php', // Appointment details page
    '/add_prescription' => 'app/doctor/add_prescription.php', // Add prescription page
    '/export' => 'app/export.php', // Export page
    

    // '/appointment' => 'appointment.php'
    // ... add more routes as needed ...
];

// Get the current request URI and remove query string
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Determine which content to load based on the route
$content_file = isset($routes[$request_uri]) ? $routes[$request_uri] : '404.php'; // Default to 404 if route not found

?>
<!DOCTYPE html>
<html>
<head>
    <title>HOSPITAL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
    <header class="header">
        <a href="/" class="logo"> <i class="fas fa-heartbeat"></i> Dr Pawan arora Clinic </a>

        <nav class="navbar">
            <a href="/dashboard">Dashboard</a>
            <a href="/about">About</a>
            <a href="/services">Services</a>
            <a href="/contact">Contact</a> 
            <a href="/doctor/profile">My Profile</a>
            <a href="/doctor/profile"><i class="bi bi-person-circle"></i></a>


            <!-- <a href="/doctors">Doctors</a> -->

            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) { ?>
                <?php if ($_SESSION['role'] == 'doctor'): ?>
                    <a href="/doctor/profile">My Profile</a> 
                <?php endif; ?>
                <a href="/app/logout.php">Logout</a> 
            <?php } else { ?>
                <a href="/app/login.php">Login</a> 
            <?php } ?>
        </nav>
        <a href="/app/appointment.php" class="link-btn">Make Appointment</a>
        <div id="menu-btn" class="fas fa-bars"></div>
    </header>

    <main>
        <?php 
        // Include the content file based on the route
        if (file_exists($content_file)) {
            include $content_file; 
        } else {
            include '404.php'; // Include a 404 page if the file doesn't exist
        }
        ?>
    </main>

    <footer>
        </footer>

    <script src="js/script.js"></script> 
</body>
</html>
