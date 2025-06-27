<?php
session_start();
require_once 'process.php';

$fuelTypes = [];
$fuelStations = [];
$priceTrends = [];

$query = "SELECT * FROM vehicle_expenses";
$result = mysqli_query($conn, $query);

if ($result) {
    $vehicleData = [];
    while ($entry = mysqli_fetch_assoc($result)) {
        $vehicleData[] = $entry;

        $type = $entry['fuel_type'];
        $station = $entry['fuel_station'];
        $cost = $entry['fuel_cost'];
        $amount = $entry['fuel_amount'];
        $date = date("Y-m-d", strtotime($entry['date_time']));

        $fuelTypes[$type] = ($fuelTypes[$type] ?? 0) + $cost;
        $fuelStations[$station] = ($fuelStations[$station] ?? 0) + $cost;

        $price = $amount > 0 ? round($cost / $amount, 2) : 0;
        $priceTrends[$type][] = ['date' => $date, 'price' => $price];
    }

    $_SESSION['vehicles'] = $vehicleData;
} else {
    echo "Error fetching data: " . mysqli_error($conn);
}

$location = "Malaybalay City, Bukidnon, Philippines";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fuel Expenses Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #121212;
            color: #eee;
            padding: 40px 20px;
        }
        .title-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 50px;
            padding: 0 20px;
            position: relative;
        }
        .title-container h2 {
            color: #ddd;
            font-size: 2rem;
            text-align: center;
        }
        .title-container img {
            position: absolute;
            right: 0;
            margin-top: 50px;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 80%;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        .menu {
            position: fixed;
            top: 0; left: 0;
            width: 250px; height: 100%;
            background-color: #1f1f1f;
            padding-top: 20px;
            z-index: 1000; display: none;
        }
        .menu a {
            display: flex;
            align-items: center;
            color: #eee;
            padding: 15px;
            text-decoration: none;
            font-size: 18px;
            transition: background-color 0.3s;
        }
        .menu a:hover { background-color: #2a2a2a; }
        .overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: none; z-index: 999;
        }
        .overlay-content {
            position: absolute; top: 20px; left: 20px;
        }
        .menu-btn {
            position: absolute; top: 20px; left: 20px;
            font-size: 30px; color: #eee; cursor: pointer;
        }
        .info-card {
            max-width: 600px; margin: 0 auto 30px auto;
            background: #1e1e1e;
            color: #eee;
            padding: 20px 25px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1rem;
        }
        .info-card span {
            font-weight: bold;
            color: #ccc;
        }
        .dashboard {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
        }
        .chart-container {
            background: #1e1e1e;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
            flex: 1 1 400px;
            max-width: 500px;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: #eee;
        }
        canvas { flex-grow: 1; }
    </style>
</head>
<body>

<div class="menu" id="sideMenu">
    <a href="add_expenses.php"><span>➕ Add Expenses</span></a>
    <a href="vehicle_expenses_page.php"><span>📋 Vehicle Expenses</span></a>
    <a href="vehicle_analytics.php"><span>📊 Vehicle Analytics</span></a>
    <a href="reports.php"><span>🗓️ Reports</span></a>
    <a href="history.php"><span>📜 History Log</span></a>
</div>

<div class="overlay" id="overlay" onclick="closeMenu()">
    <div class="overlay-content">
        <span onclick="closeMenu()" style="font-size: 40px; color: white;">&times;</span>
    </div>
</div>

<span class="menu-btn" onclick="openMenu()">&#9776; Menu</span>

<div class="title-container">
    <h2>🚗 Vehicle Fuel Expense Dashboard</h2>
     <img src="cdrrmo (2).jpg" alt="logo">
</div>

<div class="info-card">
    <div>
        <span>📍 Location:</span>
        <?= $location ?>
    </div>
    <div>
        <span>🕒 Date & Time (GMT +8):</span>
        <span id="timeDisplay">Loading...</span>
    </div>
</div>

<div class="dashboard">
    <div class="chart-container">
        <h3 style="text-align:center;">Fuel Expenses by Type</h3>
        <canvas id="fuelTypeChart"></canvas>
    </div>

    <div class="chart-container">
        <h3 style="text-align:center;">Fuel Expenses by Station</h3>
        <canvas id="fuelStationChart"></canvas>
    </div>
</div>

<script>
function openMenu() {
    document.getElementById("sideMenu").style.display = "block";
    document.getElementById("overlay").style.display = "block";
}
function closeMenu() {
    document.getElementById("sideMenu").style.display = "none";
    document.getElementById("overlay").style.display = "none";
}

function updateTime() {
    const now = new Date();
    const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
    const gmt8 = new Date(utc + (3600000 * 8));
    document.getElementById('timeDisplay').textContent = gmt8.toLocaleString('en-PH');
}
setInterval(updateTime, 1000);
updateTime();

const fuelTypeColors = ['#f94144', '#577590', '#f8961e', '#43aa8b', '#277da1', '#f3722c', '#90be6d'];
const fuelStationColors = ['#2a9d8f', '#e76f51', '#264653', '#e9c46a', '#8ecae6', '#6a4c93'];

new Chart(document.getElementById('fuelTypeChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_keys($fuelTypes)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($fuelTypes)) ?>,
            backgroundColor: fuelTypeColors,
            borderColor: '#121212',
            borderWidth: 2
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom',
                labels: { color: '#eee' }
            },
            tooltip: {
                callbacks: {
                    label: ctx => `${ctx.label}: ₱${ctx.parsed.toLocaleString()}`
                }
            }
        }
    }
});

new Chart(document.getElementById('fuelStationChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_keys($fuelStations)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($fuelStations)) ?>,
            backgroundColor: fuelStationColors,
            borderColor: '#121212',
            borderWidth: 2
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom',
                labels: { color: '#eee' }
            },
            tooltip: {
                callbacks: {
                    label: ctx => `${ctx.label}: ₱${ctx.parsed.toLocaleString()}`
                }
            }
        }
    }
});
</script>

</body>
</html>
