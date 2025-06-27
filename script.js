document.addEventListener("DOMContentLoaded", function () {

    // ✅ Render Budget Chart from database via AJAX
    const renderBudgetChart = () => {
        const ctx = document.getElementById("budgetChart")?.getContext("2d");

        if (!ctx) {
            console.warn("Chart canvas not found. Skipping chart rendering.");
            return;
        }

        fetch("get_budget.php")
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error("Server Error:", data.error);
                    return;
                }

                const totalBudget = data.totalBudget || 0;
                const totalExpenses = data.totalExpenses || 0;
                const remainingBudget = data.remainingBudget || 0;

                if (isNaN(totalBudget) || isNaN(totalExpenses) || isNaN(remainingBudget)) {
                    console.error("Invalid numeric values from backend.");
                    return;
                }

                // Destroy existing chart if it exists
                if (window.budgetChartInstance) {
                    window.budgetChartInstance.destroy();
                }

                window.budgetChartInstance = new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: ["Total Budget", "Total Expenses", "Remaining Budget"],
                        datasets: [{
                            label: "Budget Overview",
                            data: [totalBudget, totalExpenses, remainingBudget],
                            backgroundColor: ["#3498db", "#e74c3c", "#2ecc71"],
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => `₱${value.toLocaleString()}`,
                                },
                            },
                        },
                        plugins: {
                            legend: { position: "top" },
                        },
                    },
                });
            })
            .catch(error => {
                console.error("Failed to fetch budget data:", error);
            });
    };

    // ✅ Toggle Destination Fields based on Checkbox
    const destinationCheckbox = document.getElementById("destination_checkbox");
    const destinationFields = document.getElementById("destination_fields");

    if (destinationCheckbox && destinationFields) {
        const toggleDestinationFields = () => {
            destinationFields.style.display = destinationCheckbox.checked ? "block" : "none";
        };

        destinationCheckbox.addEventListener("change", toggleDestinationFields);
        toggleDestinationFields(); // Set initial visibility
    }

    // ✅ Print Vehicle Expenses with Borders
    window.printExpenses = function () {
        const printArea = document.getElementById("vehicleExpensesTable");

        if (!printArea) {
            alert("Vehicle expenses table not found.");
            return;
        }

        const printWindow = window.open("", "", "height=800,width=1200");

        printWindow.document.write(`
            <html>
            <head>
                <title>Vehicle Expenses</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h2 { text-align: center; }
                    table { width: 100%; border-collapse: collapse; border: 2px solid black; }
                    th, td { border: 2px solid black; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                </style>
            </head>
            <body>
                <h2>🚗 Vehicle Expenses</h2>
                <table>${printArea.innerHTML}</table>
                <script>
                    window.onload = function() {
                        window.print();
                        window.close();
                    };
                </script>
            </body>
            </html>
        `);

        printWindow.document.close();
    };

    // ✅ Auto-hide alerts after 5 seconds
    const autoHideAlerts = () => {
        const alerts = document.querySelectorAll(".alert");
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.display = "none";
            }, 5000);
        });
    };

    // ✅ Run on load
    renderBudgetChart();
    autoHideAlerts();
});
