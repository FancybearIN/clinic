<?php
// Include the database configuration file
include '../config/db_config.php';

// Start the session
session_start();

// Check if the request method is POST (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data and sanitize it
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash the password for security
    $role = $_POST["role"]; // Get the selected role (patient or doctor)

    try {
        // Check if the email already exists in the database
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // If an account with the same email is found
        if ($stmt->rowCount() > 0) {
            $error = "Email already exists."; 
        } else {
            // Prepare the SQL query to insert the new user into the database
            $sql = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':role', $role); 

            // If the insertion was successful
            if ($stmt->execute()) {
                // Instead of logging in automatically, redirect to login page with a success message
                $_SESSION['registration_success'] = "Account created successfully! You can now log in.";
                header("Location: login.php"); // Redirect to login page
                exit; // Terminate the script after redirection
            } else {
                // If there was an error during insertion
                $error = "Error creating account.";
            }
        }
    } catch(PDOException $e) {
        // Catch any potential database errors
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
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
        <div class="fm-box Register">
            <h2>Registration</h2>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <!-- Role Selection -->
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="patient">Patient</option>
                        <option value="doctor">Doctor</option>
                    </select>
                </div>

                <?php if (isset($error)) { echo "<p class='text-danger'>$error</p>"; } ?>

                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="agreeTerms">
                    <label class="form-check-label" for="agreeTerms">I agree to the terms & conditions</label>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>

            <div class="mt-3">
                <p>Already have an account? <a href="login.php" class="login-link">Login</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
