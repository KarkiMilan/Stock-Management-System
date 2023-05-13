<button class="button" onclick="toggleChart()">Show Stock Chart</button>

<div id="chart-container" style="display:none;">
    <canvas id="myChart"></canvas>
</div>

<div id="suggestions-container" style="display:none;">
    <h3>Stock Suggestions</h3>
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
            $result = $conn->query("SELECT date_created, quantity FROM stock_list");
            $stock_data = array();
            while ($row = $result->fetch_assoc()) {
                $stock_data[] = $row;
            }
        ?>

        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($stock_data, 'date_created')); ?>,
                datasets: [
                    {
                        label: 'Stock Quantity',
                        data: <?php echo json_encode(array_column($stock_data, 'quantity')); ?>,
                        fill: false,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2
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
                    }],
                    xAxes: [{
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 20
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
                    text: 'Stock Chart',
                    fontColor: 'black',
                    fontSize: 20
                },
                elements: {
                    point: {
                        radius: 4,
                        backgroundColor: 'rgba(75, 192, 192, 1)'
                    }
                }
            }
        });

        // Increase chart size
        myChart.canvas.parentNode.style.width = '800px';
        myChart.canvas.parentNode.style.height = '400px';
    }

    function showSuggestions() {
    // Query the database
    <?php
        $result = $conn->query("SELECT date_created, quantity FROM stock_list");
        $stock_data = array();
        while ($row = $result->fetch_assoc()) {
            $stock_data[] = $row;
        }
    ?>

    // Sort stock data by quantity
    var sortedData = <?php echo json_encode($stock_data); ?>;
    sortedData.sort(function(a, b) {
        return b.quantity - a.quantity;
    });

    // Find dates with highest, lowest, and mid-level stock
    var highestStockDate = sortedData[0].date_created;
    var lowestStockDate = sortedData[sortedData.length - 1].date_created;
    var midStockIndex = Math.round(sortedData.length / 2);
    var midStockDate = sortedData[midStockIndex].date_created;

    // Generate list of stock suggestions
    var suggestionsList = document.getElementById('suggestions-list');
    suggestionsList.innerHTML = ''; // clear previous suggestions

    // Add new suggestions
    var listItem1 = document.createElement('li');
    listItem1.textContent = 'Consider reducing stock for ' + highestStockDate + ', which has the highest stock of ' + sortedData[0].quantity + ' items';
    suggestionsList.appendChild(listItem1);

    var listItem2 = document.createElement('li');
    listItem2.textContent = 'Order more stock for ' + lowestStockDate + ', which has the lowest stock of ' + sortedData[sortedData.length - 1].quantity + ' items';
    suggestionsList.appendChild(listItem2);

    var listItem3 = document.createElement('li');
    listItem3.textContent = 'Consider restocking ' + midStockDate + ', which has a mid-level stock of ' + sortedData[midStockIndex].quantity + ' items';
    suggestionsList.appendChild(listItem3);
}


</script>
