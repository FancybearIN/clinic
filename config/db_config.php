<?php

# Database credentials
$DB_HOST="localhost";
$DB_USER="kali";
$DB_PASS="your_db_password"; # Replace with your actual database password
$DB_NAME="clinic";

try {
    $conn = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully"; // You can uncomment this for testing
} catch(PDOExcError: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'email' in 'field list'eption $e) {
    echo "Error: " . $e->getMessage();
}
?>
