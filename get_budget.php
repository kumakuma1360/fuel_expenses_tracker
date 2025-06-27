<?php
// get_budget.php
header('Content-Type: application/json');
require_once 'db.php'; // ✅ Includes your existing DB connection

// Fetch the latest budget entry
$sql = "SELECT * FROM budget ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        "totalBudget" => (float)$row["total_budget"],
        "totalExpenses" => (float)$row["total_expenses"],
        "remainingBudget" => (float)$row["remaining_budget"]
    ]);
} else {
    echo json_encode(["error" => "No budget data found."]);
}

$conn->close();
?>
