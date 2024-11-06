<button class="button" onclick="toggleChart()">Show Stock Chart</button>

<div id="chart-container" style="display:none;">
    <canvas id="myChart"></canvas>
</div>

<div id="suggestions-container" style="display:none;">
    <h3>ABC Algorithm Suggestions</h3>
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
            // Ensure quantity is treated as a float
            $row['quantity'] = (float)$row['quantity'];
            $stock_data[] = $row;
        }

        // Merge stock items by date (ignore time)
        $merged_data = array();
        foreach ($stock_data as $item) {
            $dateKey = explode(' ', $item['date_created'])[0]; // Get only the date part (YYYY-MM-DD)
            if (isset($merged_data[$dateKey])) {
                $merged_data[$dateKey] += $item['quantity']; // Accumulate quantity
            } else {
                $merged_data[$dateKey] = $item['quantity']; // Initialize with current item's quantity
            }
        }

        // Prepare data for JavaScript
        $dates = json_encode(array_keys($merged_data));
        $quantities = json_encode(array_values($merged_data));
    ?>

    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo $dates; ?>,
            datasets: [
                {
                    label: 'Stock Quantity',
                    data: <?php echo $quantities; ?>,
                    fill: false,
                    borderColor: 'rgba(255, 75, 75, 1)',
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
                    fontColor: 'red',
                    fontSize: 16
                }
            },
            title: {
                display: true,
                text: 'Stock Chart',
                fontColor: 'red',
                fontSize: 20
            },
            elements: {
                point: {
                    radius: 4,
                    backgroundColor: 'rgba(255, 75, 75, 1)'
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
        $result = $conn->query("SELECT date_created, quantity, price FROM stock_list"); // Ensure price is included
        $stock_data = array();
        while ($row = $result->fetch_assoc()) {
            // Make sure quantity and price are treated as numbers
            $row['quantity'] = (float)$row['quantity'];
            $row['price'] = (float)$row['price'];
            $stock_data[] = $row;
        }
    ?>

    // Convert PHP data to JavaScript
    var sortedData = <?php echo json_encode($stock_data); ?>;

    // Create a map to merge duplicates by date
    var uniqueItems = new Map();

    // Merge stock items by date (ignore time)
    sortedData.forEach(function(item) {
        var dateKey = item.date_created.split(' ')[0]; // Get only the date part (YYYY-MM-DD)

        // Initialize or update the existing item in the map
        if (uniqueItems.has(dateKey)) {
            var existingItem = uniqueItems.get(dateKey);
            existingItem.quantity += item.quantity; // Accumulate quantity correctly
            existingItem.totalValue += item.quantity * item.price; // Accumulate total value correctly
        } else {
            uniqueItems.set(dateKey, {
                date_created: dateKey, // Store only the date
                quantity: item.quantity, // Initialize with current item's quantity
                totalValue: item.quantity * item.price // Total value based on the current item's price
            });
        }
    });

    // Convert the map back to an array
    var mergedData = Array.from(uniqueItems.values());

    // Assign ABC categories based on contribution to total inventory value
    var totalValue = mergedData.reduce(function(accumulator, item) {
        return accumulator + item.totalValue;
    }, 0);

    // Calculate categories
    mergedData.forEach(function(item) {
        var percentage = (item.totalValue / totalValue) * 100;

        if (percentage > 70) {
            item.category = 'A'; // High value
        } else if (percentage > 20) {
            item.category = 'B'; // Moderate value
        } else {
            item.category = 'C'; // Low value
        }
    });

    // Generate list of ABC suggestions
    var suggestionsList = document.getElementById('suggestions-list');
    suggestionsList.innerHTML = ''; // Clear previous suggestions

    // Adding ABC Analysis suggestions
    mergedData.forEach(function(item) {
        var listItem = document.createElement('li');
        listItem.textContent = 'Date: ' + item.date_created + 
            ' is under Category of: ' + item.category + 
            ' with Total Merged Quantity: ' + item.quantity + 
            ' and Total Value: RS.' + item.totalValue.toFixed(0) + '.';
        suggestionsList.appendChild(listItem);
    });
}


</script>
