<?php
$host = "localhost";
$user = "root"; // or your DB username
$password = ""; // or your DB password
$database = "fuel_expenses_dashboard_db"; 

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
