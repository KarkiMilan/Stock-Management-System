<button class="button" onclick="toggleChart()">Show Purchase Chart</button>

<div id="chart-container" style="display:none;">
    <canvas id="myChart"></canvas>
    
</div>

<script>
    function toggleChart() {
        var chartContainer = document.getElementById("chart-container");
        if (chartContainer.style.display === "none") {
            chartContainer.style.display = "block";
            showChart();
        } else {
            chartContainer.style.display = "none";
        }
    }

    function showChart() {
        // Query the database
        <?php
            // Purchase data for this month
            $result_this_month = $conn->query("SELECT DATE(date_created) as purchase_date, SUM(amount) as total_purchase 
                                                FROM `purchase_order_list` 
                                                WHERE YEAR(date_created) = YEAR(CURRENT_DATE()) 
                                                AND MONTH(date_created) = MONTH(CURRENT_DATE())
                                                GROUP BY DATE(date_created) 
                                                ORDER BY DATE(date_created) ASC");
            $purchase_data_this_month = array();
            while ($row = $result_this_month->fetch_assoc()) {
                $purchase_data_this_month[] = $row;
            }

            // Purchase data for this year
            $result_this_year = $conn->query("SELECT DATE_FORMAT(date_created, '%Y-%m') as purchase_date, SUM(amount) as total_purchase 
                                                FROM `purchase_order_list` 
                                                WHERE YEAR(date_created) = YEAR(CURRENT_DATE()) 
                                                GROUP BY DATE_FORMAT(date_created, '%Y-%m')
                                                ORDER BY DATE_FORMAT(date_created, '%Y-%m') ASC");
            $purchase_data_this_year = array();
            while ($row = $result_this_year->fetch_assoc()) {
                $purchase_data_this_year[] = $row;
            }

            // Purchase data for all time
			

            $result_all_time = $conn->query("SELECT DATE(date_created) as purchase_date, SUM(amount) as total_purchase 
                                                FROM `purchase_order_list` 
                                                GROUP BY DATE(date_created) 
                                                ORDER BY SUM(amount) DESC, DATE(date_created) ASC");
            $purchase_data_all_time = array();
            while ($row = $result_all_time->fetch_assoc()) {
                $purchase_data_all_time[] = $row;
            }
        ?>

        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($purchase_data_all_time, 'purchase_date')); ?>,
                datasets: [
                    {
                        label: 'Total Purchase (This Month)',
                        data: <?php echo json_encode(array_column($purchase_data_this_month, 'total_purchase')); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Total Purchase (This Year)',
                        data: <?php echo json_encode(array_column($purchase_data_this_year, 'total_purchase')); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Total Purchase (All Time)',
                        data: <?php echo json_encode(array_column($purchase_data_all_time, 'total_purchase')); ?>,
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
                text: 'Purchase Chart',
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
</script>