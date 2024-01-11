<button class="button" onclick="toggleChart()">Show Quantity Flow Chart</button>

<div id="chart-container" style="display:none;">
    <canvas id="myChart"></canvas>
</div>
<div id="suggestions-container" style="display:none;">
    <h3>Quantity Flow Suggestions</h3>
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
        $stockResult = $conn->query("SELECT date_created, quantity FROM stock_list");
        $stock_data = array();
        while ($row = $stockResult->fetch_assoc()) {
            $stock_data[] = $row;
        }

        $purchaseResult = $conn->query("SELECT item_id, SUM(quantity) as total_quantity FROM po_items GROUP BY item_id");
        $purchase_data = array();
        while ($row = $purchaseResult->fetch_assoc()) {
            $purchase_data[] = $row;
        }
    ?>

    // Sort stock data by quantity
    var sortedStockData = <?php echo json_encode($stock_data); ?>;
    sortedStockData.sort(function(a, b) {
        return b.quantity - a.quantity;
    });

    // Find dates with highest, lowest, and mid-level stock
    var highestStockDate = sortedStockData[0].date_created;
    var lowestStockDate = sortedStockData[sortedStockData.length - 1].date_created;
    var midStockIndex = Math.round(sortedStockData.length / 2);
    var midStockDate = sortedStockData[midStockIndex].date_created;

    // Generate list of stock suggestions
    var suggestionsList = document.getElementById('suggestions-list');
    suggestionsList.innerHTML = ''; // clear previous suggestions

// Rule-based suggestion generation
    var listItem1 = document.createElement('li');
    listItem1.textContent = 'Rule 1 : Consider reducing stock for ' + highestStockDate + ', which has the highest stock of ' + sortedStockData[0].quantity + ' items';
    suggestionsList.appendChild(listItem1);

    var listItem2 = document.createElement('li');
    listItem2.textContent = 'Rule 2 : Order more stock for ' + lowestStockDate + ', which has the lowest stock of ' + sortedStockData[sortedStockData.length - 1].quantity + ' items';
    suggestionsList.appendChild(listItem2);

    var listItem3 = document.createElement('li');
    listItem3.textContent = 'Rule 3 : Consider restocking ' + midStockDate + ', which has a mid-level stock of ' + sortedStockData[midStockIndex].quantity + ' items';
    suggestionsList.appendChild(listItem3);

    var sortedPurchaseData = <?php echo json_encode($purchase_data); ?>;
    sortedPurchaseData.sort(function(a, b) {
        return b.total_quantity - a.total_quantity;
    });

    var purchaseSuggestion = document.createElement('li');
    purchaseSuggestion.textContent = 'Rule 4 : Consider purchasing less of item ' + sortedPurchaseData[0].item_id + ', which has the highest total quantity of ' + sortedPurchaseData[0].total_quantity + ' ordered';
    suggestionsList.appendChild(purchaseSuggestion);
}

</script>
