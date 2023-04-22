<button class="button" onclick="toggleChart()">Show Quantity Flow Chart</button>

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
</script>
