<?php
 include '../config/db_config.php';
 session_start();

 // ... (Authentication check for doctors) ...

 if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $patientId = $_POST['patient_id'];
     $doctorId = $_SESSION['id'];
     $medication = $_POST['medication'];
     $dosage = $_POST['dosage'];

     // ... (Input validation) ...

     try {
         $sql = "INSERT INTO prescriptions (patient_id, doctor_id, medication, dosage, date_prescribed) 
                 VALUES (:patient_id, :doctor_id, :medication, :dosage, NOW())";
         $stmt = $conn->prepare($sql);
         // ... (Bind parameters and execute) ...

         header("Location: /doctor/profile"); // Redirect back to doctor profile
         exit;
     } catch(PDOException $e) {
         // ... (Error handling) ...
     }
 }
 ?>