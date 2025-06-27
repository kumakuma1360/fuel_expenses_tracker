<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'history_functions.php';
require_once 'db.php';

date_default_timezone_set('Asia/Shanghai');

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(trim($data));
    }
}

if (!isset($_SESSION['budget'])) $_SESSION['budget'] = 0;
if (!isset($_SESSION['total_expenses'])) $_SESSION['total_expenses'] = 0;
if (!isset($_SESSION['vehicles'])) $_SESSION['vehicles'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Update budget (AJAX)
    if (isset($_POST['update_budget'])) {
        $new_budget = filter_var($_POST['new_budget'], FILTER_VALIDATE_FLOAT);

        if ($new_budget !== false) {
            $_SESSION['budget'] = $new_budget;
            $remaining_budget = $new_budget - $_SESSION['total_expenses'];

            addHistory("Updated budget to PHP " . number_format($new_budget, 2));

            $result = $conn->query("SELECT COUNT(*) as count FROM budget");
            $row = $result->fetch_assoc();

            if ($row['count'] == 0) {
                $stmt = $conn->prepare("INSERT INTO budget (total_budget, total_expenses, remaining_budget) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ddd", $new_budget, $_SESSION['total_expenses'], $remaining_budget);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                $stmt = $conn->prepare("UPDATE budget SET total_budget = ?, total_expenses = ?, remaining_budget = ? WHERE id = 1");
                if ($stmt) {
                    $stmt->bind_param("ddd", $new_budget, $_SESSION['total_expenses'], $remaining_budget);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            echo "✅ Budget updated successfully.";
            exit;
        } else {
            echo "❌ Invalid budget value.";
            exit;
        }
    }

    // ✅ Clear budget (AJAX)
    if (isset($_POST['clear_budget'])) {
        clearHistory();
        $_SESSION['budget'] = 0;
        $_SESSION['total_expenses'] = 0;
        $_SESSION['vehicles'] = [];
        addHistory("Budget and history cleared.");

        $result = $conn->query("SELECT COUNT(*) as count FROM budget");
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            $stmt = $conn->prepare("INSERT INTO budget (total_budget, total_expenses, remaining_budget) VALUES (0, 0, 0)");
            if ($stmt) {
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $stmt = $conn->prepare("UPDATE budget SET total_budget = 0, total_expenses = 0, remaining_budget = 0 WHERE id = 1");
            if ($stmt) {
                $stmt->execute();
                $stmt->close();
            }
        }

        echo "🗑️ Budget cleared successfully.";
        exit;
    }

    // ✅ Add expense (regular form submission)
    if (isset($_POST['add_expense'])) {
        $vehicle = sanitizeInput($_POST['vehicle_unit']);
        $plate = sanitizeInput($_POST['plate_number']);
        $riv_no = sanitizeInput($_POST['RIV_No']);
        $fuel_station = sanitizeInput($_POST['fuel_station']);
        $fuel_type = sanitizeInput($_POST['fuel_type']);
        $fuel_price = filter_var($_POST['fuel_price'], FILTER_VALIDATE_FLOAT);
        $fuel_amount = filter_var($_POST['fuel_amount'], FILTER_VALIDATE_FLOAT);
        $date_time = date("Y-m-d H:i:s");

        $destination_to = isset($_POST['destination_to']) ? sanitizeInput($_POST['destination_to']) : null;
        $destination_from = isset($_POST['destination_from']) ? sanitizeInput($_POST['destination_from']) : null;
        $destination = $destination_to && $destination_from ? "$destination_to - $destination_from" : null;

        if (empty($destination_to) || empty($destination_from)) {
            $_SESSION['error'] = "⚠️ Destination fields are required!";
            header("Location: vehicle_expenses_page.php");
            exit();
        }

        if ($vehicle && $plate && $riv_no && $fuel_station && $fuel_type && $fuel_price !== false && $fuel_amount !== false) {
            $fuel_cost = $fuel_price * $fuel_amount;

            $_SESSION['total_expenses'] += $fuel_cost;
            $_SESSION['vehicles'][] = [
                'vehicle' => $vehicle,
                'plate' => $plate,
                'riv_no' => $riv_no,
                'fuel_station' => $fuel_station,
                'fuel_type' => $fuel_type,
                'fuel_amount' => $fuel_amount,
                'fuel_cost' => $fuel_cost,
                'destination' => $destination,
                'date_time' => $date_time,
            ];

            addHistory("Added expense: $vehicle ($plate) - $fuel_station, $fuel_type, ₱$fuel_price x $fuel_amount L (₱$fuel_cost), Destination: $destination");

            $stmt = $conn->prepare("INSERT INTO vehicle_expenses 
                (vehicle_unit, riv_no, plate_number, fuel_station, fuel_type, fuel_amount, fuel_cost, date_time, destination)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt) {
                $stmt->bind_param("ssssssdss",
                    $vehicle, $riv_no, $plate, $fuel_station, $fuel_type,
                    $fuel_amount, $fuel_cost, $date_time, $destination
                );
                $stmt->execute();
                $stmt->close();
            }
        }

        // Redirect after traditional form submission
        header("Location: vehicle_expenses_page.php");
        exit();
    }

    // ✅ Delete grouped vehicle expenses (by vehicle and fuel type)
    if (isset($_POST['delete_group']) && isset($_POST['vehicle']) && isset($_POST['fuel_type'])) {
        $vehicle = sanitizeInput($_POST['vehicle']);
        $fuel_type = sanitizeInput($_POST['fuel_type']);

        $stmt = $conn->prepare("DELETE FROM vehicle_expenses WHERE vehicle_unit = ? AND fuel_type = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $vehicle, $fuel_type);
            $stmt->execute();
            $stmt->close();

            addHistory("Deleted all expenses for $vehicle ($fuel_type)");
            $_SESSION['message'] = "🗑️ All entries for $vehicle using $fuel_type were deleted.";
            header("Location: vehicle_expenses_page.php");
            exit();
        } else {
            $_SESSION['error'] = "❌ Failed to delete entries for $vehicle ($fuel_type): " . $conn->error;
            header("Location: vehicle_expenses_page.php");
            exit();
        }
    }

    // ❌ Unknown or invalid request
    echo "❌ Unknown request.";
    exit();
}
?>
