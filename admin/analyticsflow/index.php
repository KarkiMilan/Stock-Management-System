<button class="button" onclick="toggleChart()">Show Quantity Flow Chart</button>

<div id="chart-container" style="display:none;">
    <canvas id="myChart"></canvas>
</div>
<div id="suggestions-container" style="display:none;">
    <h3>Safety Stock Calculation Algorithm Suggestions</h3>
    <ul id="suggestions-list"></ul>
</div>
<script>
    function toggleChart() {
        var chartContainer = document.getElementById("chart-container");
        var suggestionsContainer = document.getElementById("suggestions-container");
        if (chartContainer.style.display === "none") {
            chartContainer.style.display = "block";
            showChart();
            suggestionsContainer.style.display = "block";
            showSuggestions();
        } else {
            chartContainer.style.display = "none";
            suggestionsContainer.style.display = "none";
        }
    }

    function showChart() {
        // Query the database
        <?php
        
            $result = $conn->query("SELECT item_id, SUM(quantity) as total_quantity FROM po_items GROUP BY item_id");
            $purchase_data = array();
            while ($row = $result->fetch_assoc()) {
                $purchase_data[] = $row;
            }
        ?>

        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: <?php echo json_encode(array_column($purchase_data, 'item_id')); ?>,
                datasets: [
                    {
                        label: 'Total Quantity Flow',
                        data: <?php echo json_encode(array_column($purchase_data, 'total_quantity')); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                legend: {
                    labels: {
                        fontColor: 'black',
                        fontSize: 16
                    }
                },
                title: {
                    display: true,
                    text: 'Quantity flow',
                    fontColor: 'black',
                    fontSize: 20
                },
                elements: {
                    point: {
                        radius: 4,
                        backgroundColor: 'rgba(255, 99, 132, 1)'
                    }
                }
            }
        });

        // Increase chart size
        myChart.canvas.parentNode.style.width = '800px';
        myChart.canvas.parentNode.style.height = '400px';
    }
    function showSuggestions() {
    // Query the stock and purchase databases
    <?php
        // Fetch stock data from the database
        $stockResult = $conn->query("SELECT date_created, quantity FROM stock_list");
        $stock_data = array();
        while ($row = $stockResult->fetch_assoc()) {
            $stock_data[] = $row;
        }

        // Fetch purchase data
        $purchaseResult = $conn->query("SELECT item_id, SUM(quantity) as total_quantity FROM po_items GROUP BY item_id");
        $purchase_data = array();
        while ($row = $purchaseResult->fetch_assoc()) {
            $purchase_data[] = $row;
        }

        // Calculate average and standard deviation for safety stock calculation
        $quantities = array_column($stock_data, 'quantity');
        $average_demand = array_sum($quantities) / count($quantities);
        
        // Calculate standard deviation
        $squared_diff = array_map(function($q) use ($average_demand) {
            return pow($q - $average_demand, 2);
        }, $quantities);
        $std_deviation = sqrt(array_sum($squared_diff) / count($squared_diff));

        // Assuming a service level of 95% (Z-score of 1.64)
        $service_level = 1.64;
        $safety_stock = $service_level * $std_deviation;
    ?>

    // Safety stock suggestion generation
    var suggestionsList = document.getElementById('suggestions-list');
    suggestionsList.innerHTML = ''; // Clear previous suggestions

    // Suggestion based on safety stock calculation
    var safetyStockSuggestion = document.createElement('li');
    safetyStockSuggestion.textContent = 'Consider maintaining a safety stock level of approximately ' + Math.round(<?php echo $safety_stock; ?>) + ' items based on your current stock levels and demand variability.';
    suggestionsList.appendChild(safetyStockSuggestion);

    // Additional suggestion for items needing reorder
    var threshold = <?php echo $safety_stock; ?>; // Reorder threshold based on safety stock
    var reorderSuggestion = document.createElement('li');
    reorderSuggestion.textContent = 'If stock falls below ' + threshold + ' items, consider placing an order to replenish stock.';
    suggestionsList.appendChild(reorderSuggestion);
}


</script>
