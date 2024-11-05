<?php
include './config/db_config.php';
session_start();

// Check if the user is logged in and is a doctor
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: doctor_dashboard.php");
    exit;
}

// Fetch doctor's name (assuming you store it in the session after login)
$doctorName = $_SESSION["username"]; 

// Handle new doctor registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_doctor'])) {
    // ... (Code for adding a new doctor - see step 4) ...
    // ... (Inside doctor_dashboard.php, within the POST handling) ...

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_doctor'])) {
        $doctorName = $_POST["doctorName"];
        $doctorPosition = $_POST["doctorPosition"];
        $doctorEmail = $_POST["doctorEmail"];
        $doctorPassword = password_hash($_POST["doctorPassword"], PASSWORD_DEFAULT);
    
        try {
            // Check if email already exists
            $sql = "SELECT * FROM doctors WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $doctorEmail);
            $stmt->execute();
    
            if ($stmt->rowCount() > 0) {
                $error = "Email already exists.";
            } else {
                // Insert new doctor into database
                $sql = "INSERT INTO doctors (name, position, email, password) 
                        VALUES (:name, :position, :email, :password)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $doctorName);
                $stmt->bindParam(':position', $doctorPosition);
                $stmt->bindParam(':email', $doctorEmail);
                $stmt->bindParam(':password', $doctorPassword);
    
                if ($stmt->execute()) {
                    $success = "New doctor added successfully!";
                } else {
                    $error = "Error adding new doctor.";
                }
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
}

// Fetch appointments for the logged-in doctor
$sql = "SELECT a.*, d.name AS doctor_name 
        FROM appointments a
        LEFT JOIN doctors d ON a.doctor_id = d.id
        WHERE a.doctor_id = :doctor_id"; // Assuming you'll store doctor ID in the session
$stmt = $conn->prepare($sql);
$stmt->bindParam(':doctor_id', $_SESSION['id']); // Assuming doctor ID is in the session
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
 

?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        /* Add custom styles here if needed */
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, Dr. <?php echo $doctorName; ?>!</h2>

        <h3>Appointments</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient Name</th>
                    <th>Age</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Reason</th>
                    <th>Time Slot</th>
                    <th>Doctor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo $appointment['id']; ?></td>
                        <td><?php echo $appointment['name']; ?></td>
                        <td><?php echo $appointment['age']; ?></td>
                        <td><?php echo $appointment['email']; ?></td>
                        <td><?php echo $appointment['phone']; ?></td>
                        <td><?php echo $appointment['reason']; ?></td>
                        <td><?php echo $appointment['timeslot']; ?></td>
                        <td><?php echo $appointment['doctor_name']; ?></td> 
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="/app/export.php" class="btn btn-primary">Download Appointments (CSV)</a>

        <h3>Add New Doctor</h3>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="doctorName">Doctor Name:</label>
                <input type="text" class="form-control" id="doctorName" name="doctorName" required>
            </div>
            <div class="form-group">
                <label for="doctorPosition">Position:</label>
                <input type="text" class="form-control" id="doctorPosition" name="doctorPosition" required>
            </div>
            <div class="form-group">
                <label for="doctorEmail">Email:</label>
                <input type="email" class="form-control" id="doctorEmail" name="doctorEmail" required>
            </div>
            <div class="form-group">
                <label for="doctorPassword">Password:</label>
                <input type="password" class="form-control" id="doctorPassword" name="doctorPassword" required>
            </div>
            <button type="submit" name="add_doctor" class="btn btn-success">Add Doctor</button>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
