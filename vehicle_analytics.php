<?php
session_start();
require 'process.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Vehicle Analytics</title>
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
        .main-content {
            padding: 20px;
        }

        .container {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Add some print-specific styling */
        @media print {
            body * {
                visibility: hidden;
            }

            .printable, .printable * {
                visibility: visible;
            }

            .printable {
                position: absolute;
                left: 0;
                top: 0;
            }

            table {
                border: 1px solid black;
            }

            th, td {
                border: 1px solid black;
                padding: 10px;
            }
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
            <a href="reports.php">🗓️ Reports</a>
            <a href="history.php">📜 History Log</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">

        <h2>Vehicle Analytics</h2>

        <!-- Vehicle Selection Form -->
        <form method="GET" action="">
            <label for="vehicle_unit">Select Vehicle:</label>
            <select id="vehicle_unit" name="vehicle_unit" required>
                <option value="">-- Choose a Vehicle --</option>
                <?php
                $vehicles = ['USAR1', 'USAR2', 'Ambulance', 'Van', 'Mobile','Others'];
                foreach ($vehicles as $vehicle) {
                    $selected = (isset($_GET['vehicle_unit']) && $_GET['vehicle_unit'] == $vehicle) ? 'selected' : '';
                    echo "<option value=\"$vehicle\" $selected>$vehicle</option>";
                }
                ?>
            </select>
            <button type="submit">View Analytics</button>
        </form>

        <?php
        if (isset($_GET['vehicle_unit'])):
            $vehicleUnit = htmlspecialchars($_GET['vehicle_unit']);
            $vehiclesData = $_SESSION['vehicles'] ?? [];

            // Filter vehicle data
            $filteredData = array_filter($vehiclesData, function ($v) use ($vehicleUnit) {
                return $v['vehicle_unit'] === $vehicleUnit;
            });

            if (count($filteredData) > 0):
                $totalFuelCost = array_sum(array_column($filteredData, 'fuel_cost'));
                $totalFuelAmount = array_sum(array_column($filteredData, 'fuel_amount'));
                $averageFuelCost = $totalFuelAmount > 0 ? $totalFuelCost / $totalFuelAmount : 0;

                // Most used fuel station
                $fuelStationCount = array_count_values(array_column($filteredData, 'fuel_station'));
                $mostUsedStation = array_search(max($fuelStationCount), $fuelStationCount);

                // Most traveled destination (Fixed code)
                $destinations = array_column($filteredData, 'destination');
                $destinationCount = array_count_values($destinations);
                $mostTravelDestination = !empty($destinationCount) ? array_search(max($destinationCount), $destinationCount) : 'N/A';

                $detailedExpenses = [];
                foreach ($filteredData as $entry) {
                    $detailedExpenses[] = [
                        'date' => date('Y-m-d', strtotime($entry['date_time'])),
                        'fuel_station' => $entry['fuel_station'],
                        'fuel_amount' => $entry['fuel_amount'],
                        'fuel_cost' => $entry['fuel_cost']
                    ];
                }
        ?>

        <div class="container">

            <!-- Fuel Analytics Table -->
            <h3>Fuel Analytics for <?= htmlspecialchars($vehicleUnit); ?></h3>
            <table class="printable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Fuel Station</th>
                        <th>Fuel Amount (L)</th>
                        <th>Fuel Cost (PHP)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detailedExpenses as $entry): ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['date']); ?></td>
                            <td><?= htmlspecialchars($entry['fuel_station']); ?></td>
                            <td><?= number_format($entry['fuel_amount'], 2); ?></td>
                            <td><?= number_format($entry['fuel_cost'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Total Row -->
                    <tr>
                        <th colspan="2">Total</th>
                        <th><?= number_format($totalFuelAmount, 2); ?> L</th>
                        <th>PHP <?= number_format($totalFuelCost, 2); ?></th>
                    </tr>
                </tbody>
            </table>

            <!-- Summary -->
            <h3>Summary for <?= htmlspecialchars($vehicleUnit); ?></h3>
            <table>
                <tr>
                    <th>Total Fuel Cost</th>
                    <td>PHP <?= number_format($totalFuelCost, 2); ?></td>
                </tr>
                <tr>
                    <th>Total Fuel Amount</th>
                    <td><?= number_format($totalFuelAmount, 2); ?> L</td>
                </tr>
                <tr>
                    <th>Average Fuel Cost per Liter</th>
                    <td>PHP <?= number_format($averageFuelCost, 2); ?></td>
                </tr>
                <tr>
                    <th>Most Used Fuel Station</th>
                    <td><?= htmlspecialchars($mostUsedStation); ?></td>
                </tr>
                <tr>
                    <th>Most Travel Destination</th>
                    <td><?= htmlspecialchars($mostTravelDestination); ?></td>
                </tr>
            </table>

        </div>

        <?php
            else:
                echo "<p>No data available for $vehicleUnit.</p>";
            endif;
        endif;
        ?>

    </div>

    <script>
        // Print functionality triggered by Ctrl+P
        document.addEventListener('keydown', function (event) {
            if (event.ctrlKey && event.key === 'p') {
                event.preventDefault();
                window.print();  // Trigger the print dialog
            }
        });
    </script>

</body>

</html>
