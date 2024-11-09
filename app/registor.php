<?php
include '../config/db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash the password
    $role = $_POST["role"]; // Get the selected role from the form

    try {
        // Check if email already exists
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "Email already exists.";
        } else {
            // Insert new user into database
            $sql = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)"; // Include role in the query
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':role', $role); // Bind the role parameter
            $stmt->bindParam(':full_name', $username); // Use username for full_name

            if ($stmt->execute()) {
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $conn->lastInsertId();
                $_SESSION["username"] = $username;
                $_SESSION["role"] = $role; // Store the role in the session

                   // Redirect based on user role
                 if ($role == 'doctor') {
                        header("Location: /doctor/doctor_dashboard.php"); // Redirect to doctor dashboard
                    } else {
                        header("Location: /patient/profile.php");  // Redirect to patient profile page
                    }
            exit;
            } else {
                $error = "Error creating account.";
            }
        }
    } catch(PDOException $e) {
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
