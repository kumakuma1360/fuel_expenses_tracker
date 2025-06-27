<?php
session_start();
require 'db.php'; // DB Connection file

function getMonthlyReport($conn) {
    $sql = "SELECT 
                DATE_FORMAT(date_time, '%M %Y') AS report_month,
                SUM(fuel_cost) AS total_cost,
                SUM(fuel_amount) AS total_amount
            FROM vehicle_expenses
            GROUP BY YEAR(date_time), MONTH(date_time)
            ORDER BY YEAR(date_time) DESC, MONTH(date_time) DESC";
    return $conn->query($sql);
}

function getYearlyReport($conn) {
    $sql = "SELECT 
                YEAR(date_time) AS report_year,
                SUM(fuel_cost) AS total_cost,
                SUM(fuel_amount) AS total_amount
            FROM vehicle_expenses
            GROUP BY YEAR(date_time)
            ORDER BY YEAR(date_time) DESC";
    return $conn->query($sql);
}

$monthlyReport = getMonthlyReport($conn);
$yearlyReport = getYearlyReport($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Expense Reports</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        /* Navbar styling */
        .navbar {
            width: 100%;
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .navbar h1 {
            margin: 0;
            font-size: 24px;
        }

        /* Main content styling */
        .content {
            padding: 20px;
        }

        .tab-button {
            padding: 10px 20px;
            margin: 5px 5px 20px 0;
            border: none;
            background-color: #ccc;
            border-radius: 5px;
            cursor: pointer;
        }

        .tab-button.active {
            background-color: #007bff;
            color: white;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .report-table th, .report-table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .report-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

<!-- Navbar -->
<div class="navbar">
    <div>
        <a href="index.php">🏠 Home</a>
        <a href="add_expenses.php">➕ Add Expenses</a>
        <a href="vehicle_expenses_page.php">📋 Vehicle Expenses</a>
        <a href="vehicle_analytics.php">📊 Vehicle Analytics</a>
        <a href="history.php">📜 History</a>
    </div>
</div>

<!-- Page Content -->
<div class="content">
    <h2>Fuel Expense Reports</h2>

    <!-- Tabs -->
    <div>
        <button class="tab-button active" onclick="showTab('monthly')">Monthly Report</button>
        <button class="tab-button" onclick="showTab('yearly')">Yearly Report</button>
    </div>

    <!-- Monthly Report -->
    <div id="monthly" class="tab-content">
        <h3>Monthly Report</h3>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Total Fuel Cost (₱)</th>
                    <th>Total Fuel Amount (Liters)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($monthlyReport && $monthlyReport->num_rows > 0): ?>
                    <?php while ($row = $monthlyReport->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['report_month']); ?></td>
                            <td><?php echo number_format($row['total_cost'], 2); ?></td>
                            <td><?php echo number_format($row['total_amount'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">No monthly data available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Yearly Report -->
    <div id="yearly" class="tab-content" style="display: none;">
        <h3>Yearly Report</h3>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Total Fuel Cost (₱)</th>
                    <th>Total Fuel Amount (Liters)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($yearlyReport && $yearlyReport->num_rows > 0): ?>
                    <?php while ($row = $yearlyReport->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['report_year']); ?></td>
                            <td><?php echo number_format($row['total_cost'], 2); ?></td>
                            <td><?php echo number_format($row['total_amount'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">No yearly data available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showTab(tab) {
    document.getElementById('monthly').style.display = (tab === 'monthly') ? 'block' : 'none';
    document.getElementById('yearly').style.display = (tab === 'yearly') ? 'block' : 'none';

    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(button => button.classList.remove('active'));

    if (tab === 'monthly') {
        buttons[0].classList.add('active');
    } else {
        buttons[1].classList.add('active');
    }
}
</script>

</body>
</html>
