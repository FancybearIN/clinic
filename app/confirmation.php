<!DOCTYPE html>
<html>
<head>
    <title>Appointment Confirmation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"> 
    <style>
        body {
            font-family: 'Arial', sans-serif; 
        }
        .confirmation-container {
            background-color: #f8f9fa; 
            padding: 40px;
            border-radius: 10px;
            margin-top: 50px;
        }
        h1 {
            color: #007bff; 
        }
        .appointment-info {
            margin-top: 30px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container"> 
        <div class="row">
            <div class="col-md-8 offset-md-2 confirmation-container"> 
                <h1 class="text-center">Appointment Confirmed!</h1>

                <?php
                include '../config/db_config.php';
                session_start();

                // Assuming you have the appointment ID in a session variable
                if (isset($_SESSION['last_appointment_id'])) {
                    $appointmentId = $_SESSION['last_appointment_id'];

                    try {
                        $sql = "SELECT start_time FROM appointments WHERE id = :id";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':id', $appointmentId);
                        $stmt->execute();
                        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($appointment) {
                            $appointmentTime = date("l, F j, Y \a\\t g:i A", strtotime($appointment['start_time']));
                            echo "<p class='text-center appointment-info'>Your appointment is scheduled for: <strong>$appointmentTime</strong></p>";
                        } else {
                            echo "<p class='text-center text-danger'>Error retrieving appointment details.</p>";
                        }
                    } catch(PDOException $e) {
                        echo "<p class='text-center text-danger'>Error: " . $e->getMessage() . "</p>";
                    }
                } else {
                    echo "<p class='text-center text-danger'>Appointment ID not found.</p>";
                }
                ?>

                <p class="text-center">Thank you for booking an appointment. We will contact you shortly to confirm.</p>

                <div class="text-center mt-4"> 
                    <a href="#" class="btn btn-primary btn-lg mr-2"><i class="fab fa-facebook-f"></i> Facebook</a>
                    <a href="#" class="btn btn-info btn-lg mr-2"><i class="fab fa-twitter"></i> Twitter</a>
                    <a href="#" class="btn btn-danger btn-lg"><i class="fab fa-instagram"></i> Instagram</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
