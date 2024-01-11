<button class="button" onclick="toggleChart()">Show Sales Chart</button>

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
            // Sales data for this month
$result_this_month = $conn->query("SELECT DATE_FORMAT(date_created, '%Y-%m-%d (%a)') as sales_date, SUM(amount) as total_sales 
                                    FROM `sales_list` 
                                    WHERE 
                                 MONTH(date_created) BETWEEN 1 AND MONTH(CURRENT_DATE())
                                    AND DATE(date_created) <= CURRENT_DATE()
                                    GROUP BY  MONTH(date_created), DATE(date_created) 
                                    ORDER BY  MONTH(date_created) ASC, DATE(date_created) ASC");

$sales_data_this_month = array();
while ($row = $result_this_month->fetch_assoc()) {
$sales_data_this_month[] = $row;
}

// Sales data for this year
$result_this_year = $conn->query("SELECT DATE_FORMAT(date_created, '%Y') as sales_date, SUM(amount) as total_sales 
FROM `sales_list` 
WHERE YEAR(date_created) = YEAR(CURRENT_DATE()) 
GROUP BY YEAR(date_created)
ORDER BY YEAR(date_created) ASC");
$sales_data_this_year = array();
while ($row = $result_this_year->fetch_assoc()) {
$sales_data_this_year[] = $row;
}

            // Sales data for all time
            $result_all_time = $conn->query("SELECT DATE_FORMAT(date_created, '%Y-%m-%d (%a)') as sales_date, SUM(amount) as total_sales 
            FROM `sales_list` 
            GROUP BY YEAR(date_created), MONTH(date_created), DATE(date_created)
            ORDER BY YEAR(date_created) ASC, MONTH(date_created) ASC, DATE(date_created) ASC");
            $sales_data_all_time = array();
            while ($row = $result_all_time->fetch_assoc()) {
                $sales_data_all_time[] = $row;
            }
        ?>

var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($sales_data_all_time, 'sales_date')); ?>,
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
                text: 'Sales Chart',
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

<h3>Sales Suggestions</h3>
<?php
$stmt = $conn->prepare("SELECT DATE(date_created) as sales_date, SUM(amount) as total_sales
FROM sales_list
GROUP BY DATE(date_created)
ORDER BY SUM(amount) DESC, DATE(date_created) ASC");
$stmt->execute();
$result_all_time = $stmt->get_result();

$sales_data_all_time = array();
while ($row = $result_all_time->fetch_assoc()) {
$sales_data_all_time[$row['sales_date']] = $row['total_sales'];
}

// Calculate total sales for all time
$total_sales_all_time = array_reduce($sales_data_all_time, function($acc, $value) {
return $acc + $value;
});

// Calculate all-time high and low sales
$all_time_high = max($sales_data_all_time);
$all_time_low = min($sales_data_all_time);

// Sort the sales data array in descending order by key
krsort($sales_data_all_time);

// Get the latest month's sales data
$current_month_date = array_key_first($sales_data_all_time);
$current_month_sales = $sales_data_all_time[$current_month_date];

$average_sales_all_time = $total_sales_all_time / count($sales_data_all_time);

$current_year_sales = 0;
foreach ($sales_data_all_time as $date => $sales_amount) {
if (substr($date, 0, 4) == substr($current_month_date, 0, 4)) {
$current_year_sales += $sales_amount;
} else {
break;
}
}

$sales_summary = array(
"current_month_sales" => $current_month_sales,
"average_sales_all_time" => $average_sales_all_time,
"current_year_sales" => $current_year_sales,
"all_time_high" => $all_time_high,
"all_time_low" => $all_time_low
);

// Rule-based suggestion generation
$suggestion = "";
if ($current_month_sales < $average_sales_all_time) {
$suggestion = "Rule 1 : Consider increasing your sales efforts to meet your demand.";
} elseif ($current_month_sales > $current_year_sales) {
$suggestion = "Rule 2 : Consider decreasing your sales efforts to avoid overselling.";
} else {
$suggestion = "Rule 3 : our current sales level seems to be appropriate. Keep up the good work!";
}

// Rule-based message generation
$sales_message = "Your current monthly sales is " . number_format($current_month_sales, 2) . ".\n";
$sales_message .= "Your average monthly sales is " . number_format($average_sales_all_time, 2) . ".\n";
$sales_message .= "Your current yearly sales is " . number_format($current_year_sales, 2) . ".\n";
$sales_message .= "Your all-time high sales is " . number_format($all_time_high, 2) . ".\n";
$sales_message .= "Your all-time low sales is " . number_format($all_time_low, 2) . ".\n";
$sales_message .= "Your sales amount for " . $all_time_high . " is high compared to historical data.\n";
$sales_message .= $suggestion;

echo "<div id='sales-suggestion'>" . nl2br($sales_message) . "</div>";

?>

