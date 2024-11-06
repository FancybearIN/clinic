<?php
include '../config/db_config.php'; // Include the database configuration file
session_start(); // Start a new session or resume an existing one

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Check if the form was submitted using POST
    $email = $_POST["email"]; // Get the email from the form
    $password = $_POST["password"]; // Get the password from the form

    try {
        // Prepare and execute a SQL query to fetch the user with the given email
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the user data as an associative array

        // Check if a user with the given email exists and if the password matches
        if ($user && password_verify($password, $user['password'])) { 
            // If login is successful:

            // Start Session Management
            $_SESSION["loggedin"] = true; // Set the 'loggedin' session variable to true
            $_SESSION["id"] = $user["id"]; // Store the user's ID in the session
            $_SESSION["username"] = $user["username"]; // Store the user's username in the session
            // End Session Management

            // Start Role-Based Redirect
            if ($user['role'] == 'doctor') { 
                header("Location: /app/doctor/doctor_dashboard.php"); // Redirect to doctor dashboard
            } else {
                header("Location: /app/patient/profile.php");  // Redirect to patient profile
            }
            exit; // Stop further script execution after redirect
            // End Role-Based Redirect

        } else {
            // If login fails:
            $error = "Invalid email or password."; // Set an error message
        }
    } catch(PDOException $e) {
        // If there's a database error:
        $error = "Error: " . $e->getMessage(); // Set an error message with the exception details
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
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
    <div class="wrap">
        <div class="fm-box login">
            <h2>Login</h2>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <?php if (isset($error)) { echo "<p class='text-danger'>$error</p>"; } ?>

                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <div class="mt-3">
            <p>Don't have an account? <a href="/app/registor.php" class="register-link">Register</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
