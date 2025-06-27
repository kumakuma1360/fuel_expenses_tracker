<div class="sidebar">
    <!-- Menu Button and Title (Inline Alignment) -->
    <div style="display: flex; align-items: center; gap: 10px;">
        <button id="menuToggle" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0; line-height: 1;">
            ☰
        </button>
        <h2 style="margin: 0px; font-size: 24px; line-height: 1;">Fuel Dashboard</h2>
    </div>

    <!-- Update Budget -->
    <form id="updateBudgetForm" method="POST">
        <label for="new_budget">Update Budget:</label>
        <input type="number" id="new_budget" name="new_budget" step="0.01" required>
        <button type="submit" name="update_budget">Update Budget</button>
    </form>

    <!-- Add Space -->
    <div style="margin: 20px 0;"></div>

    <!-- Clear Budget -->
    <form id="clearBudgetForm" method="POST">
        <button type="submit" name="clear_budget" style="padding: 8px 16px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer;">🗑️ Clear Budget</button>
    </form>

    <!-- Budget Summary -->
    <h3>Budget Overview</h3>
    <p><strong>Total Budget:</strong> <?= number_format($_SESSION['budget'], 2); ?></p>
    <p><strong>Total Expenses:</strong> <?= number_format($_SESSION['total_expenses'], 2); ?></p>
    <p><strong>Remaining Budget:</strong> <?= number_format($_SESSION['budget'] - $_SESSION['total_expenses'], 2); ?></p>

    <!-- Chart Container -->
    <div class="chart-container">
        <canvas id="budgetChart"></canvas>
    </div>
</div>

<!-- Overlay Menu -->
<div id="overlayMenu" style="
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100%;
    background: rgba(44, 62, 80, 0.95);
    color: white;
    padding: 20px;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.5);
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
    z-index: 999;">
    <button id="closeMenu" style="background: none; border: none; color: white; font-size: 28px; cursor: pointer;">
        ✖
    </button>
    <a href="index.php" style="color: white; text-decoration: none; display: block; padding: 10px 0;">🏠 Home</a>
    <a href="vehicle_expenses_page.php" style="color: white; text-decoration: none; display: block; padding: 10px 0;">📋 Vehicle Expenses</a>
    <a href="vehicle_analytics.php" style="color: white; text-decoration: none; display: block; padding: 10px 0;">📊 Vehicle Analytics</a>
    <a href="reports.php" style="color: white; text-decoration: none; display: block; padding: 10px 0;">🗓️ Reports</a>
    <a href="history.php" style="color: white; text-decoration: none; display: block; padding: 10px 0;">🕒 View History</a>
</div>

<!-- Chart Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Chart Initialization
        const ctx = document.getElementById('budgetChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Total Budget', 'Total Expenses', 'Remaining Budget'],
                    datasets: [{
                        label: 'Budget Overview',
                        data: [
                            <?= $_SESSION['budget']; ?>,
                            <?= $_SESSION['total_expenses']; ?>,
                            <?= $_SESSION['budget'] - $_SESSION['total_expenses']; ?>
                        ],
                        backgroundColor: ['#3498db', '#e74c3c', '#2ecc71']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Toggle Overlay Menu
        const menuToggle = document.getElementById('menuToggle');
        const overlayMenu = document.getElementById('overlayMenu');
        const closeMenu = document.getElementById('closeMenu');

        // Open the overlay menu when the menu button is clicked
        menuToggle.addEventListener('click', () => {
            overlayMenu.style.transform = 'translateX(0)';
        });

        // Close the overlay menu when the close button is clicked
        closeMenu.addEventListener('click', () => {
            overlayMenu.style.transform = 'translateX(-100%)';
        });

        // Close the overlay menu when clicking outside the menu area (on the overlay)
        overlayMenu.addEventListener('click', (event) => {
            // Check if the click was inside the menu
            if (!event.target.closest('#overlayMenu')) {
                overlayMenu.style.transform = 'translateX(-100%)';
            }
        });

        // Handle Update Budget with AJAX
        document.getElementById('updateBudgetForm').addEventListener('submit', function (e) {
            e.preventDefault();  // Prevent form from submitting traditionally
            
            const newBudget = document.getElementById('new_budget').value;
            
            const formData = new FormData();
            formData.append('update_budget', true);
            formData.append('new_budget', newBudget);
            
            fetch('process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Handle success, update the page with the new budget
                console.log('Budget updated:', data);
                alert('Budget updated successfully!');
                location.reload();  // Refresh the page to reflect the updated budget
            })
            .catch(error => {
                console.error('Error updating budget:', error);
            });
        });

        // Handle Clear Budget with AJAX
        document.getElementById('clearBudgetForm').addEventListener('submit', function (e) {
            e.preventDefault();  // Prevent form from submitting traditionally
            
            if (confirm('Are you sure you want to clear the budget?')) {
                fetch('process.php', {
                    method: 'POST',
                    body: new URLSearchParams({ 'clear_budget': true })
                })
                .then(response => response.text())
                .then(data => {
                    console.log('Budget cleared:', data);
                    alert('Budget cleared successfully!');
                    location.reload();  // Refresh the page to reflect the cleared budget
                })
                .catch(error => {
                    console.error('Error clearing budget:', error);
                });
            }
        });
    });
</script>
