<?php
// Include database configuration
include 'config/db_config.php'; 
session_start();

// --- ROUTING ---
// Define routes and their corresponding content files
$routes = [
    '/' => 'app/index.php', // Home page
    '/dashboard' => 'app/dashboard.php', // Dashboard page
    '/about' => 'app/about.php', // About page
    '/services' => 'app/services.php', // Services page
    '/contact' => 'app/contact.php', // Contact page
    '/login' => 'app/login.php', // Login page
    '/registor' => 'app/registor.php', // Registration page
    '/doctor/profile' => 'app/doctor/doctor_profile.php', // Doctor profile page
    '/doctor/doctor_dashboard.php' => 'app/doctor/doctor_dashboard.php', // Doctor dashboard page <--- Added route
    '/patient/profile' => 'app/patient/profile.php', // Patient profile page
    '/patient/records/(.*)' => 'app/patient/record.php', // Patient records page
    '/appointment/details/(.*)' => 'app/appointment_details.php', // Appointment details page
    '/add_prescription' => 'app/doctor/add_prescription.php', // Add prescription page
    '/export' => 'app/export.php', // Export page
];


// Get the current request URI and remove query string
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Determine which content to load based on the route
$content_file = isset($routes[$request_uri]) ? $routes[$request_uri] : '404.php'; // Default to 404 if route not found

// --- DATA FETCHING ---
// Fetch doctor and patient counts
try {
    $totalDoctors = $conn->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
    $totalPatients = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn();
} catch (PDOException $e) {
    $error = "Error fetching data: " . $e->getMessage();
}

// Fetch doctor profiles for animation (assuming you have a 'doctors' table)
try {
    $doctorProfiles = $conn->query("SELECT * FROM doctors")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching doctor profiles: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>HOSPITAL</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        body {
                background-color: white; /* Set background color to white */
        }
        /* Doctor Profile Animation */
        #doctor-profile-container {
            position: relative;
            height: 300px; /* Adjust as needed */
            overflow: hidden;
        }

        .doctor-profile {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .doctor-profile.active {
            opacity: 1;
        }
         /* About Us Text Size */
         .about .content { /* Target the content within the about section */
            font-size: 1.2em; /* Adjust the font size as needed */
        }

        /* Booking Section Styles */
        .booking {
            padding: 50px 0; /* Add padding to the section */
            text-align: center; /* Center align content */
        }

        .booking img {
            max-width: 300px; /* Adjust image size as needed */
            margin-bottom: 20px;
        }
          /*
        Doctor Theme Colors
        .home {
            background: linear-gradient(to bottom, #007bff, #90caf9); /* Blue gradient */
            color: white;
         */
         /*  
        .link-btn {
            background-color: #2196F3; /* Blue button */
            color: white;
        */
    </style>
</head>
<body>
    <!-- --- HEADER SECTION --- -->
    <header class="header">
        <a href="/" class="logo"> <i class="fas fa-heartbeat"></i> Dr Pawan arora Clinic </a>

        <nav class="navbar">
            <a href="/dashboard">Dashboard</a>
            <a href="/about">About</a>
            <a href="/services">Services</a>
            <a href="/contact">Contact</a> 

            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) { ?>
                <?php if ($_SESSION['role'] == 'doctor'): ?>
                    <a href="/doctor/profile">My Profile</a> 
                <?php endif; ?>
                <a href="/app/logout.php">Logout</a> 
            <?php } else { ?>
                <a href="/app/login.php">Login</a> 
            <?php } ?>
        </nav>

        <a href="app/appointment.php" class="link-btn">Make Appointment</a>

        <div id="menu-btn" class="fas fa-bars"></div>
    </header>

    <!-- --- MAIN CONTENT SECTION --- -->
    <main>
     <!-- --- HOME SECTION --- -->
     <section class="home" id="home">
            <div class="container">
                <div class="row min-vh-100 align-items-center">
                    <div class="col-md-6 content">
                        <h3>We take care of your healthy life</h3>
                        <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Laudantium itaque, ullam distinctio veritatis excepturi, aperiam, dolorum culpa consequuntur quos saepe iure. Excepturi, velit saepe.</p>
                        <a href="app/appointment.php" class="link-btn">Make Appointment</a>
                    </div>

                    <div class="col-md-6">
                        <div id="doctor-profile-container">
                            <img src="image/home.svg" alt="Book Appointment" class="img-fluid" style="max-width: 700px;"> 
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- --- ABOUT SECTION --- -->
          <!-- --- ABOUT SECTION --- -->
          <section class="about" id="about">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 image">
                        <img src="image/about.svg" class="w-100 mb-5 mb-md-0" alt="About Us"> 
                    </div>

                    <div class="col-md-6 content">
                        <span>about us</span>
                        <h3>Caring for you is our priority</h3>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestiae reiciendis accusamus fugit necessitatibus nemo illum, repudiandae quos, magni iusto ullam. Minima assumenda saepe culpa praesentium. Esse adipisci animi voluptates!</p>
                        <a href="/about" class="link-btn">learn more</a> 
                    </div>
                </div>
            </div>
        </section>

        <!-- --- BOOKING SECTION --- -->
        <section class="booking" id="booking">
            <div class="container">
                <img src="image/book.svg" alt="Book Appointment" class="img-fluid" style="max-width: 700px;">
                <h2>Need an Appointment?</h2>class="img-fluid" style="max-width: 700px;
                <p>Book your appointment online with ease.</p>
                <a href="app/appointment.php" class="link-btn">Book Now</a>
            </div>
        </section>

        <!-- --- STATS SECTION --- -->
        <section class="icons-container">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 icon">
                        <i class="fas fa-user-md"></i>
                        <h3><?php echo $totalDoctors; ?>+</h3>
                        <p>doctors at work</p>
                    </div>

                    <div class="col-md-3 icon">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $totalPatients; ?>+</h3>
                        <p>satisfied patients</p>
                    </div>

                    <div class="col-md-3 icon">
                        <i class="fas fa-procedures"></i>
                        <h3>500+</h3>
                        <p>bed facility</p>
                    </div>

                    <div class="col-md-3 icon">
                        <i class="fas fa-hospital"></i>
                        <h3>80+</h3>
                        <p>available hospitals</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- --- SERVICES SECTION --- -->
       <!-- <section class="services" id="services">
            <h1 class="heading"> our <span>services</span> </h1>

            <div class="box-container container">

                <div class="box">
                    <i class="fas fa-notes-medical"></i>
                    <h3>free checkups</h3>
                    <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ad, omnis.</p>
                    <a href="/services" class="link-btn"> learn more </a>
                </div>

                <div class="box">
                    <i class="fas fa-ambulance"></i>
                    <h3>24/7 ambulance</h3>
                    <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ad, omnis.</p>
                    <a href="/services" class="link-btn"> learn more </a>
                </div>

                <div class="box">
                    <i class="fas fa-user-md"></i>
                    <h3>expert doctors</h3>
                    <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ad, omnis.</p>
                    <a href="/services" class="link-btn"> learn more </a>
                </div>

                <div class="box">
                    <i class="fas fa-pills"></i>
                    <h3>medicines</h3>
                    <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ad, omnis.</p>
                    <a href="/services" class="link-btn"> learn more </a>
                </div>

                <div class="box">
                    <i class="fas fa-procedures"></i>
                    <h3>bed facility</h3>
                    <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ad, omnis.</p>
                    <a href="/services" class="link-btn"> learn more </a>
                </div>

                <div class="box">
                    <i class="fas fa-heartbeat"></i>
                    <h3>total care</h3>
                    <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ad, omnis.</p>
                    <a href="/services" class="link-btn"> learn more </a>
                </div>

            </div>

        </section> -->

        <!-- --- FOOTER SECTION --- -->
        <section class="footer">

            <div class="box-container container">

                <div class="box">
                    <h3>quick links</h3>
                    <a href="/"> <i class="fas fa-chevron-right"></i> home </a>
                    <a href="/services"> <i class="fas fa-chevron-right"></i> services </a>
                    <a href="/about"> <i class="fas fa-chevron-right"></i> about </a>
                    <a href="/doctors"> <i class="fas fa-chevron-right"></i> doctors </a>
                    <a href="/book"> <i class="fas fa-chevron-right"></i> book </a>
                    <a href="/review"> <i class="fas fa-chevron-right"></i> review </a>
                    <a href="/blogs"> <i class="fas fa-chevron-right"></i> blogs </a>
                </div>

                <div class="box">
                    <h3>our services</h3>
                    <a href="#"> <i class="fas fa-chevron-right"></i> dental care </a>
                    <a href="#"> <i class="fas fa-chevron-right"></i> message therapy </a>
                    <a href="#"> <i class="fas fa-chevron-right"></i> cardioloty </a>
                    <a href="#"> <i class="fas fa-chevron-right"></i> diagnosis </a>
                    <a href="#"> <i class="fas fa-chevron-right"></i> ambulance service </a>
                </div>

                <div class="box">
                    <h3>contact info</h3>
                    <a href="#"> <i class="fas fa-phone"></i> +123-456-7890 </a>
                    <a href="#"> <i class="fas fa-phone"></i> +111-222-3333 </a>
                    <a href="#"> <i class="fas fa-envelope"></i> students@gndec.ac.in </a>
                    <a href="#"> <i class="fas fa-envelope"></i> students@gndec.ac.in </a>
                    <a href="#"> <i class="fas fa-map-marker-alt"></i> ludhiana, india - 141418 </a>
                </div>

                <div class="box">
                    <h3>follow us</h3>
                    <a href="#"> <i class="fab fa-facebook-f"></i> facebook </a>
                    <a href="#"> <i class="fab fa-twitter"></i> twitter </a>
                    <a href="#"> <i class="fab fa-instagram"></i> instagram </a>
                    <a href="#"> <i class="fab fa-linkedin"></i> linkedin </a>
                    <a href="#"> <i class="fab fa-pinterest"></i> pinterest </a>
                </div>

            </div>

            <div class="credit"> created by <span>GNDEC</span> | all rights reserved </div>

        </section>
    </main>

    <!-- --- 404 SECTION (Outside <main> to display even if content file is not found) --- -->
    <!-- <?php 
    if (file_exists($content_file)) {
        include $content_file; 
    } else {
        include '404.php'; // Include a 404 page if the file doesn't exist
    }
    ?> -->

    <!-- --- FOOTER SECTION --- -->
    <footer>
        </footer>

    <script src="js/script.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            let currentDoctorIndex = 0;
            const doctorProfiles = $('.doctor-profile');

            function showDoctorProfile() {
                doctorProfiles.removeClass('active');
                $(doctorProfiles[currentDoctorIndex]).addClass('active');
                currentDoctorIndex = (currentDoctorIndex + 1) % doctorProfiles.length;
            }

            showDoctorProfile(); // Show the first profile initially
            setInterval(showDoctorProfile, 10000); // Rotate every 10 seconds
        });
    </script>
</body>
</html>
