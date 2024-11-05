<?php

$servername = "172.16.246.133";
$username = "root";
$password = "kali";
$dbname = "clinic";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully"; // You can uncomment this for testing
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
