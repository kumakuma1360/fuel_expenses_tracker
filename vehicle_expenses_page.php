<?php
session_start();
require_once 'process.php'; // Make sure $conn exists and connected to DB

// Handle delete request (you can move this to process.php if preferred)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_group'])) {
    $vehicle = $_POST['vehicle'] ?? '';
    $fuel_type = $_POST['fuel_type'] ?? '';

    if ($vehicle && $fuel_type) {
        // Use prepared statements to avoid SQL injection
        $stmt = $conn->prepare("DELETE FROM vehicle_expenses WHERE vehicle_unit = ? AND fuel_type = ?");
        $stmt->bind_param("ss", $vehicle, $fuel_type);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Deleted expenses for vehicle '$vehicle' with fuel type '$fuel_type'.";
        } else {
            $_SESSION['error'] = "Failed to delete expenses: " . $conn->error;
        }
        $stmt->close();
        // Redirect to avoid resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['error'] = "Invalid vehicle or fuel type for deletion.";
    }
}

// Initialize
$groupedData = [];
$totalFuelCost = 0;
$totalFuelAmount = 0;

// Fetch data from database
$query = "SELECT * FROM vehicle_expenses";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $key = $row['vehicle_unit'] . '_' . $row['fuel_type']; // Group by vehicle and fuel type

        if (!isset($groupedData[$key])) {
            $groupedData[$key] = [
                'vehicle'       => $row['vehicle_unit'],
                'fuel_type'     => $row['fuel_type'],
                'fuel_amount'   => 0,
                'fuel_cost'     => 0,
                'riv_no'        => $row['riv_no'],
                'plate'         => $row['plate_number'],
                'fuel_station'  => $row['fuel_station'],
                'date_time'     => $row['date_time'],
                'destination'   => $row['destination']
            ];
        }
        $groupedData[$key]['fuel_amount'] += $row['fuel_amount'];
        $groupedData[$key]['fuel_cost']   += $row['fuel_cost'];
    }
} else {
    echo "Error fetching data: " . mysqli_error($conn);
}

// Calculate totals
foreach ($groupedData as $data) {
    $totalFuelAmount += $data['fuel_amount'];
    $totalFuelCost   += $data['fuel_cost'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vehicle Expenses</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .navbar {
            background: #2c3e50;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .navbar a {
            text-decoration: none;
            color: white;
            margin: 0 10px;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .navbar a:hover {
            background: #2980b9;
        }
        .main-content {
            padding: 20px;
            background: white;
            margin: 20px auto;
            max-width: 1200px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            text-align: left;
            padding: 8px 12px;
            border: 1px solid #ddd;
        }
        input[type=text], input[type=number] {
            width: 100%;
            padding: 4px;
            border: none;
            background: transparent;
            outline: none;
        }
        input[readonly] {
            color: #555;
        }
        button {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            background: #3498db;
            color: white;
            cursor: pointer;
            transition: 0.3s ease;
        }
        button.delete-btn {
            background: #e74c3c;
        }
        .printable {
            margin: 10px 0;
        }
        @media print {
            .navbar, button, #searchBox {
                display: none;
            }
            table {
                page-break-inside: avoid;
            }
        }
        .error-message {
            background: #e74c3c;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .success-message {
            background: #2ecc71;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        #searchBox {
            padding: 8px;
            width: 100%;
            max-width: 400px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .total-row th, .total-row td {
            color: black;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div>
            <a href="index.php">🏠 Home</a>
            <a href="add_expenses.php">➕ Add Expenses</a>
            <a href="vehicle_analytics.php">📊 Vehicle Analytics</a>
            <a href="reports.php">🗓️ Reports</a>
            <a href="history.php">📜 History Log</a>
        </div>
    </div>

    <div class="main-content">
        <h2>📋 Vehicle Expenses</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= $_SESSION['error']; ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?= $_SESSION['success']; ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (empty($groupedData)): ?>
            <p>No vehicle expenses recorded.</p>
        <?php else: ?>
            <button onclick="window.print()" class="printable">🖨️ Print Expenses</button>
            <input type="text" id="searchBox" onkeyup="filterTable()" placeholder="Search by any field...">

            <table class="printable" id="expensesTable">
                <thead>
                    <tr>
                        <th>Vehicle Unit</th>
                        <th>RIV No</th>
                        <th>Plate Number</th>
                        <th>Fuel Station</th>
                        <th>Fuel Type</th>
                        <th>Fuel Amount (L)</th>
                        <th>Fuel Cost (PHP)</th>
                        <th>Date & Time</th>
                        <th>Destination</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($groupedData as $data): ?>
                    <tr>
                        <?php foreach (['vehicle', 'riv_no', 'plate', 'fuel_station', 'fuel_type', 'fuel_amount', 'fuel_cost', 'date_time', 'destination'] as $field): ?>
                            <td>
                                <input 
                                    type="<?= ($field === 'fuel_amount' || $field === 'fuel_cost') ? 'number' : 'text'; ?>" 
                                    step="<?= ($field === 'fuel_amount' || $field === 'fuel_cost') ? '0.01' : ''; ?>"
                                    value="<?= htmlspecialchars($data[$field]); ?>" 
                                    readonly
                                >
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete all entries for <?= htmlspecialchars($data['vehicle']); ?> with fuel type <?= htmlspecialchars($data['fuel_type']); ?>?');">
                                <input type="hidden" name="vehicle" value="<?= htmlspecialchars($data['vehicle']); ?>">
                                <input type="hidden" name="fuel_type" value="<?= htmlspecialchars($data['fuel_type']); ?>">
                                <button type="submit" name="delete_group" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <th colspan="7">Total Fuel Cost:</th>
                    <th colspan="3">PHP <?= number_format($totalFuelCost, 2); ?></th>
                </tr>
                <tr class="total-row">
                    <th colspan="7">Total Fuel Amount:</th>
                    <th colspan="3"><?= number_format($totalFuelAmount, 2); ?> L</th>
                </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function filterTable() {
            let input = document.getElementById("searchBox").value.toUpperCase();
            let tr = document.querySelectorAll("#expensesTable tbody tr");

            tr.forEach(row => {
                // Skip total rows from filtering
                if(row.classList.contains('total-row')) return;

                let found = false;
                row.querySelectorAll("td").forEach(td => {
                    let inputEl = td.querySelector("input");
                    let text = inputEl ? inputEl.value.toUpperCase() : td.textContent.toUpperCase();
                    if (text.includes(input)) {
                        found = true;
                    }
                });
                row.style.display = found ? "" : "none";
            });
        }
    </script>
</body>
</html>
