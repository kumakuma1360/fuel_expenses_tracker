<?php
session_start();
require_once 'process.php'; // Ensure $conn is initialized

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_key'])) {
    $key = $_POST['delete_key'];
    $parts = explode('|', $key);

    if (count($parts) === 2) {
        $vehicleUnit = $parts[0];
        $fuelType = $parts[1];

        $stmt = $conn->prepare("DELETE FROM vehicle_expenses WHERE vehicle_unit = ? AND fuel_type = ?");
        $stmt->bind_param("ss", $vehicleUnit, $fuelType);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $_SESSION['success'] = "✅ Deleted all expenses for $vehicleUnit using $fuelType.";
        } else {
            $_SESSION['error'] = "❌ No matching records found to delete.";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "❌ Invalid key format.";
    }
} else {
    $_SESSION['error'] = "❌ Unknown request.";
}

header("Location: vehicle_expenses_page.php");
exit();
