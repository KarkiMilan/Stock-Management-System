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
$result_this_month = $conn->query("SELECT DATE_FORMAT(date_created, '%Y-%m-%d (%a)') as purchase_date, SUM(amount) as total_purchase 
                                    FROM `purchase_order_list` 
                                    WHERE 
                                 MONTH(date_created) BETWEEN 1 AND MONTH(CURRENT_DATE())
                                    AND DATE(date_created) <= CURRENT_DATE()
                                    GROUP BY  MONTH(date_created), DATE(date_created) 
                                    ORDER BY  MONTH(date_created) ASC, DATE(date_created) ASC");

$purchase_data_this_month = array();
while ($row = $result_this_month->fetch_assoc()) {
    $purchase_data_this_month[] = $row;
}

// Purchase data for this year
$result_this_year = $conn->query("SELECT DATE_FORMAT(date_created, '%Y') as purchase_date, SUM(amount) as total_purchase 
                                    FROM `purchase_order_list` 
                                    WHERE YEAR(date_created) = YEAR(CURRENT_DATE()) 
                                    GROUP BY YEAR(date_created)
                                    ORDER BY YEAR(date_created) ASC");
$purchase_data_this_year = array();
while ($row = $result_this_year->fetch_assoc()) {
    $purchase_data_this_year[] = $row;
}

// Purchase data for all time
$result_all_time = $conn->query("SELECT DATE_FORMAT(date_created, '%Y-%m-%d (%a)') as purchase_date, SUM(amount) as total_purchase 
                                    FROM `purchase_order_list` 
                                    GROUP BY YEAR(date_created), MONTH(date_created), DATE(date_created)
                                    ORDER BY YEAR(date_created) ASC, MONTH(date_created) ASC, DATE(date_created) ASC");
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

<h3>Purchase Suggestions</h3>
<?php

$stmt = $conn->prepare("SELECT DATE(date_created) as purchase_date, SUM(amount) as total_purchase 
FROM `purchase_order_list` 
GROUP BY DATE(date_created) 
ORDER BY SUM(amount) DESC, DATE(date_created) ASC");
$stmt->execute();
$result_all_time = $stmt->get_result();

$purchase_data_all_time = array();
while ($row = $result_all_time->fetch_assoc()) {
$purchase_data_all_time[$row['purchase_date']] = $row['total_purchase'];
}

// Calculate total purchase for all time
$total_purchase_all_time = array_reduce($purchase_data_all_time, function($acc, $value) {
return $acc + $value;
});

// Calculate all-time high and low purchases
$all_time_high = max($purchase_data_all_time);
$all_time_low = min($purchase_data_all_time);

// Sort the purchase data array in descending order by key
krsort($purchase_data_all_time);

// Get the latest month's purchase data
$current_month_date = array_key_first($purchase_data_all_time);
$current_month_purchase = $purchase_data_all_time[$current_month_date];

$average_purchase_all_time = $total_purchase_all_time / count($purchase_data_all_time);

$current_year_purchase = 0;
foreach ($purchase_data_all_time as $date => $purchase_amount) {
if (substr($date, 0, 4) == substr($current_month_date, 0, 4)) {
$current_year_purchase += $purchase_amount;
} else {
break;
}
}

$purchase_summary = array(
"current_month_purchase" => $current_month_purchase,
"average_purchase_all_time" => $average_purchase_all_time,
"current_year_purchase" => $current_year_purchase,
"all_time_high" => $all_time_high,
"all_time_low" => $all_time_low
);

$suggestion = "";
if ($current_month_purchase < $average_purchase_all_time) {
$suggestion = "Consider increasing your purchases to meet your demand.";
} elseif ($current_month_purchase > $current_year_purchase) {
$suggestion = "Consider decreasing your purchases to avoid surplus inventory.";
} else {
$suggestion = "Your current purchase level seems to be appropriate. Keep up the good work!";
}

$purchase_message = "Your current monthly purchase is " . number_format($current_month_purchase, 2) . ".\n";
$purchase_message .= "Your average monthly purchase is " . number_format($average_purchase_all_time, 2) . ".\n";
$purchase_message .= "Your current yearly purchase is " . number_format($current_year_purchase, 2) . ".\n";
$purchase_message .= "Your all-time high purchase is " . number_format($all_time_high, 2) . ".\n";
$purchase_message .= "Your all-time low purchase is " . number_format($all_time_low, 2) . ".\n";
$purchase_message .= "Your purchase amount for " . $all_time_high . " is high compared to historical data.\n";
$purchase_message .= $suggestion;

    echo "<div id='purchase-suggestion'>" . nl2br($purchase_message) . "</div>";

?>