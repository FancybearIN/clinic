<?php
include '../config/db_config.php';
session_start();
// gandhi edits

// ... (Authentication check) ...

$appointmentId = $_GET['id']; // Get appointment ID from URL

// Fetch appointment details
$sql = "SELECT a.*, p.name AS patient_name, d.name AS doctor_name FROM appointments a JOIN users p ON a.patient_id = p.id JOIN doctors d ON a.doctor_id = d.id WHERE a.id = :appointment_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':appointment_id', $appointmentId);
$stmt->execute();
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

// ... (Error handling if appointment is not found) ...

// ... (Display appointment details) ...

// ... (If the logged-in user is the assigned doctor, display prescription form and link to patient records) ...
?>
