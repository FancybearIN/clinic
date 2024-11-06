<?php
include '../config/db_config.php';
session_start();

$name = $age = $email = $phone = $reason = $timeslot = "";
$error = "";
$success = ""; // Initialize $success

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $age = $_POST["age"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $reason = $_POST["reason"];
    $timeslot = $_POST["timeslot"];

    // Split the selected time slot into start and end times
    list($startTime, $endTime) = explode(" - ", $timeslot);

    // Format the start and end times as valid DATETIME values
    $startTime = date("Y-m-d H:i:s", strtotime($startTime));
    $endTime = date("Y-m-d H:i:s", strtotime($endTime));

    // Basic input validation (you should add more robust validation)
    if (empty($name) || empty($email) || empty($phone)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            // Check if an appointment with the same email and phone already exists
            $checkSql = "SELECT id FROM appointments WHERE email = :email AND phone = :phone AND (status = 'pending' OR status = 'confirmed')";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bindParam(':email', $email);
            $checkStmt->bindParam(':phone', $phone);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                $error = "An appointment with this email and phone number already exists.";
            } else {
                // Prepare and execute the SQL query to insert data
                $sql = "INSERT INTO appointments (name, age, email, phone, reason, start_time, end_time) 
                        VALUES (:name, :age, :email, :phone, :reason, :start_time, :end_time)";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':age', $age);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':reason', $reason);
                $stmt->bindParam(':start_time', $startTime);
                $stmt->bindParam(':end_time', $endTime);

                if ($stmt->execute()) {
                    $success = "Appointment booked successfully!";
                    // Reset form fields after successful submission
                    $name = $age = $email = $phone = $reason = $timeslot = "";

                    // Store the last inserted appointment ID in the session
                    $_SESSION['last_appointment_id'] = $conn->lastInsertId();

                    // Redirect AFTER successful booking
                    header("Location: confirmation.php");
                    exit; // Important: Stop further script execution
                } else {
                    $error = "Error booking appointment.";
                }
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #e0f2f7; 
            font-family: 'Arial', sans-serif;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .appointment-form {
            width: 100%;
            max-width: 500px;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2196F3; 
            text-align: center;
            margin-bottom: 30px;
        }

        .btn-primary {
            background-color: #2196F3; 
            border-color: #2196F3;
        }

        /* Background Image */
        .appointment-form::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('doctor-background.jpg'); /* Replace with your image */
            background-size: cover;
            background-position: center;
            opacity: 0.15; 
            z-index: -1; 
            filter: blur(2px); /* Add a slight blur to the background */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="appointment-form"> 
            <h2>Book Appointment</h2>

            <?php if (!empty($error)) { echo "<p class='text-danger'>$error</p>"; } ?>
            <?php if (isset($success)) { echo "<p class='text-success'>$success</p>"; } ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
                </div>
                <div class="form-group">
                    <label for="age">Age:</label>
                    <input type="number" class="form-control" id="age" name="age" value="<?php echo $age; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $phone; ?>" required>
                </div>
                <div class="form-group">
                    <label for="reason">Reason for Appointment:</label>
                    <textarea class="form-control" id="reason" name="reason"><?php echo $reason; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="timeslot">Preferred Time Slot:</label>
                    <select class="form-control" id="timeslot" name="timeslot" required>
                        <option value="">Select Time Slot</option>
                        <option value="9:30 AM - 12:30 PM" <?php if ($timeslot == "9:30 AM - 12:30 PM") echo "selected"; ?>>9:30 AM - 12:30 PM</option>
                        <option value="2:30 PM - 4:30 PM" <?php if ($timeslot == "2:30 PM - 4:30 PM") echo "selected"; ?>>2:30 PM - 4:30 PM</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Book Appointment</button> 
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
