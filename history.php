<?php
session_start();
require_once 'history_functions.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>📜 History Log - Fuel Expenses Tracker</title>
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
            <a href="reports.php">🗓️ Reports</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>📜 History</h2>

        <!-- Display History -->
        <div class="container">
            <?php displayHistory(); ?>
        </div>
    </div>

</body>

</html>
