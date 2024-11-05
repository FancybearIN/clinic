<?php
include 'db_config.php';

// Set headers to download as CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=appointments.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, array('Name', 'Age', 'Email', 'Phone', 'Reason', 'Time Slot', 'Created At'));

// Fetch data from the database
$sql = "SELECT * FROM appointments ORDER BY created_at DESC";
$stmt = $conn->query($sql);

// Modify the output to include the doctor's name
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, array(
            $row['name'], 
            $row['age'], 
            $row['email'], 
            $row['phone'], 
            $row['reason'], 
            $row['timeslot'],
            $row['doctor_name'], // Add doctor name to CSV
            $row['created_at']
        ));
    }
// Fetch data from the database (for the logged-in doctor)
$sql = "SELECT * FROM appointments WHERE doctor_id = :doctor_id ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':doctor_id', $_SESSION['id']); // Assuming doctor ID is in the session
$stmt->execute();
// Close the output stream
fclose($output);
?>
