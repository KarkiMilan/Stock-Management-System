<button class="button" onclick="toggleChart()">Show Top Seller</button>

<div id="chart-container" style="display:none;">
    <canvas id="myChart"></canvas>
    <div id="sales-data-suggestions" style="margin-top: 20px;">
        <p><strong>Sales Suggestions:</strong></p>
        <p id="suggestions-list"></p>

    </div>
</div>

<script>
    function toggleChart() {
        var chartContainer = document.getElementById("chart-container");
        if (chartContainer.style.display === "none") {
            chartContainer.style.display = "block";
            showChart();
            showSuggestions(); // Call to show sales suggestions
        } else {
            chartContainer.style.display = "none";
        }
    }

    function showChart() {
        // Query the database
        <?php
        // Sales data for this month
        $result_this_month = $conn->query("SELECT SUM(amount) as total_sales, client 
        FROM `sales_list` 
        WHERE YEAR(date_created) = YEAR(CURRENT_DATE()) 
        AND MONTH(date_created) = MONTH(CURRENT_DATE())
        GROUP BY client 
        ORDER BY SUM(amount) DESC");
        $sales_data_this_month = array();
        while ($row = $result_this_month->fetch_assoc()) {
            $sales_data_this_month[] = $row;
        }

        // Sales data for this year
        $result_this_year = $conn->query("SELECT SUM(amount) as total_sales, client 
        FROM `sales_list` 
        WHERE YEAR(date_created) = YEAR(CURRENT_DATE()) 
        GROUP BY client 
        ORDER BY SUM(amount) DESC");
        $sales_data_this_year = array();
        while ($row = $result_this_year->fetch_assoc()) {
            $sales_data_this_year[] = $row;
        }

        // Sales data for all time
        $result_all_time = $conn->query("SELECT SUM(amount) as total_sales, client 
        FROM `sales_list` 
        GROUP BY client 
        ORDER BY SUM(amount) DESC");
        $sales_data_all_time = array();
        while ($row = $result_all_time->fetch_assoc()) {
            $sales_data_all_time[] = $row;
        }
        ?>

        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($sales_data_all_time, 'client')); ?>,
                datasets: [
                    {
                        label: 'Total Sales (This Month)',
                        data: <?php echo json_encode(array_column($sales_data_this_month, 'total_sales')); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Total Sales (This Year)',
                        data: <?php echo json_encode(array_column($sales_data_this_year, 'total_sales')); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Total Sales (All Time)',
                        data: <?php echo json_encode(array_column($sales_data_all_time, 'total_sales')); ?>,
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        borderColor: 'rgba(255, 206, 86, 1)',
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
                    text: 'Top Seller',
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
        myChart.canvas.parentNode.style.height = '350px';
    }

    function showSuggestions() {
        // Sales suggestions from PHP
        <?php
        $max_sales_this_year = 0;
        $max_sales_all_time = 0;
        $min_sales_this_year = PHP_INT_MAX;
        $min_sales_all_time = PHP_INT_MAX;
        $total_sales = 0;
        $growth_potential = [];

        foreach ($sales_data_all_time as $data) {
            $total_sales += $data['total_sales'];
            $total_sales_this_year = 0;
            $total_sales_all_time = $data['total_sales'];

            // Find sales for the same client this year
            foreach ($sales_data_this_year as $d) {
                if ($d['client'] === $data['client']) {
                    $total_sales_this_year = $d['total_sales'];
                    break;
                }
            }

            // Calculate percentage growth between this year and all time
            $growth_percent = ($total_sales_this_year / $total_sales_all_time) * 100;
            $growth_potential[] = [
                'client' => $data['client'],
                'growth_percent' => $growth_percent,
                'total_sales_this_year' => $total_sales_this_year,
                'total_sales_all_time' => $total_sales_all_time
            ];

            // Check for highest and lowest sales this year and all time
            if ($total_sales_this_year > $max_sales_this_year) {
                $max_sales_this_year = $total_sales_this_year;
                $max_sales_this_year_client = $data['client'];
            }
            if ($total_sales_all_time > $max_sales_all_time) {
                $max_sales_all_time = $total_sales_all_time;
                $max_sales_all_time_client = $data['client'];
            }
            if ($total_sales_this_year < $min_sales_this_year) {
                $min_sales_this_year = $total_sales_this_year;
                $min_sales_this_year_client = $data['client'];
            }
            if ($total_sales_all_time < $min_sales_all_time) {
                $min_sales_all_time = $total_sales_all_time;
                $min_sales_all_time_client = $data['client'];
            }
        }

        // Sort clients by growth potential (descending order)
        usort($growth_potential, function($a, $b) {
            return $b['growth_percent'] - $a['growth_percent'];
        });

        // Top client with highest growth
        $top_growth_client = $growth_potential[0]['client'] ?? 'N/A';
        $top_growth_percent = $growth_potential[0]['growth_percent'] ?? 0;
        ?>
        
        var suggestionsList = document.getElementById('suggestions-list');
        suggestionsList.innerHTML = ''; // clear previous suggestions

        var listItem5 = document.createElement('li');
        listItem5.textContent = 'Rule 1: To catch up to <?php echo $max_sales_this_year_client; ?>, <?php echo $min_sales_this_year_client; ?> needs to increase their sales by <?php echo number_format($max_sales_this_year - $min_sales_this_year, 2); ?> this year.';
        suggestionsList.appendChild(listItem5);

        var listItem6 = document.createElement('li');
        listItem6.textContent = 'Rule 2: To catch up to <?php echo $max_sales_all_time_client; ?>, <?php echo $min_sales_all_time_client; ?> needs to increase their sales by <?php echo number_format($max_sales_all_time - $min_sales_all_time, 2); ?> in total sales.';
        suggestionsList.appendChild(listItem6);

        var listItem7 = document.createElement('li');
        listItem7.textContent = 'Rule 3: The client with the most growth potential is <?php echo $top_growth_client; ?>, with a growth percentage of <?php echo number_format($top_growth_percent, 2); ?>% this year.';
        suggestionsList.appendChild(listItem7);
    
        // Growth Recommendations
        var listItem9 = document.createElement('div');
        listItem9.innerHTML = '<strong>Growth Recommendations:</strong>';
        suggestionsList.appendChild(listItem9);
        
        <?php foreach ($growth_potential as $client_data): ?>
            var listItem = document.createElement('li');
            listItem.textContent = '<?php echo $client_data['client']; ?>: <?php echo number_format($client_data['growth_percent'], 2); ?>% growth (<?php echo number_format($client_data['total_sales_this_year'], 2); ?> this year, <?php echo number_format($client_data['total_sales_all_time'], 2); ?> all time)';
            suggestionsList.appendChild(listItem);
        <?php endforeach; ?>
    }
    
</script>
