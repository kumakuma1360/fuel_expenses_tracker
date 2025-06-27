<?php
session_start();
require 'process.php';
require_once 'history_functions.php'; // Include the new history functions
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Expenses Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js" defer></script>
    <style>
        /* Add a little styling for the To and From fields */
        .destination-fields {
            display: none;
            margin-top: 10px;
        }

        .destination-fields input {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="content">

        <!-- Fuel Expense Form -->
        <div class="card">
            <h2>Add Fuel Expense</h2>
            <form method="POST" action="process.php">

                <!-- Vehicle Unit -->
                <label for="vehicle_unit">Vehicle Unit:</label>
                <select id="vehicle_unit" name="vehicle_unit" required>
                    <option value="USAR1">USAR1</option>
                    <option value="USAR2">USAR2</option>
                    <option value="Ambulance">Ambulance</option>
                    <option value="Van">Van</option>
                    <option value="Mobile">Mobile</option>
                    <option value="Others">Others</option>
                </select>

                <!-- Plate Number -->
                <label for="plate_number">Plate Number:</label>
                <input type="text" id="plate_number" name="plate_number" maxlength="10" required>

                <!-- RIV Number -->
                <label for="RIV_No">RIV No:</label>
                <input type="text" id="RIV_No" name="RIV_No" maxlength="12" required>

                <!-- Fuel Station -->
                <label for="fuel_station">Fuel Station:</label>
                <select id="fuel_station" name="fuel_station" required>
                    <option value="Petron">Petron</option>
                    <option value="Shell">Shell</option>
                    <option value="Caltex">Caltex</option>
                    <option value="Velox">Velox</option>
                </select>

                <!-- Fuel Type -->
                <label for="fuel_type">Fuel Type:</label>
                <select id="fuel_type" name="fuel_type" required>
                    <option value="Unleaded">Unleaded</option>
                    <option value="Premium">Premium</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Kerosene">Kerosene</option>
                </select>

                <!-- Fuel Price -->
                <label for="fuel_price">Fuel Price (per liter):</label>
                <input type="number" id="fuel_price" name="fuel_price" step="0.01" required>

                <!-- Fuel Amount -->
                <label for="fuel_amount">Fuel Amount (liters):</label>
                <input type="number" id="fuel_amount" name="fuel_amount" step="0.01" required>

                <!-- Destination Trigger -->
                <label for="destination">Destination:</label>
                <input type="checkbox" id="destination_checkbox" name="destination_checkbox"> Click to add destination

                <!-- Destination Fields (hidden initially) -->
                <div class="destination-fields" id="destinationFields">
                    <label for="destination_to">From:</label>
                    <input type="text" id="destination_to" name="destination_to" placeholder="Enter destination to" />

                    <label for="destination_from">To:</label>
                    <input type="text" id="destination_from" name="destination_from" placeholder="Enter destination from" />
                </div>

                <!-- Submit Button -->
                <button type="submit" name="add_expense">Add Expense</button>
            </form>
        </div>

    </div>

    <script>
        // Show/Hide Destination fields based on checkbox click
        document.getElementById('destination_checkbox').addEventListener('change', function() {
            const destinationFields = document.getElementById('destinationFields');
            if (this.checked) {
                destinationFields.style.display = 'block';
            } else {
                destinationFields.style.display = 'none';
            }
        });
    </script>

</body>

</html>
