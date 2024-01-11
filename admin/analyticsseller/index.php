<button class="button" onclick="toggleChart()">Show Top Seller</button>

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
    myChart.canvas.parentNode.style.height = '400px';
}
</script>
<style>
  table {
    border-collapse: collapse;
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #333;
    display: none;
  }
  thead {
    background-color: #f7f7f7;
  }
  th, td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
  }
  th {
    font-weight: bold;
    text-transform: uppercase;
  }
  tbody tr:nth-child(even) {
    background-color: #f2f2f2;
  }
  tbody tr:hover {
    background-color: #ddd;
  }
  .show-table {
    display: block;
  }
  #sales-data-suggestions {
    max-width: 800px;
    margin: 20px auto;
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #333;
  }
  #sales-data-suggestions p {
    margin: 10px 0;
  }
  #sales-data-suggestions p strong {
    font-weight: bold;
  }
</style>
<button id="toggle-table-btn">Show Sales Data</button>
<table id="sales-data-table">
  <thead>
    <tr>
      <th>Client</th>
      <th>Total Sales This Month</th>
      <th>Total Sales This Year</th>
      <th>Total Sales All Time</th>
    </tr>
  </thead>
  <tbody>
    <?php
    foreach ($sales_data_all_time as $data) {
      $client = $data['client'];
      $total_sales_this_month = 0;
      $total_sales_this_year = 0;
      $total_sales_all_time = $data['total_sales'];
      foreach ($sales_data_this_month as $d) {
        if ($d['client'] === $client) {
          $total_sales_this_month = $d['total_sales'];
          break;
        }
      }
      foreach ($sales_data_this_year as $d) {
        if ($d['client'] === $client) {
          $total_sales_this_year = $d['total_sales'];
          break;
        }
      }
      ?>
      <tr>
        <td><?php echo $client; ?></td>
        <td><?php echo number_format($total_sales_this_month, 2); ?></td>
        <td><?php echo number_format($total_sales_this_year, 2); ?></td>
        <td><?php echo number_format($total_sales_all_time, 2); ?></td>
      </tr>
      <?php
    }
    ?>
  </tbody>
  
</table>
<div id="sales-data-suggestions">
  <p><strong>Sales Suggestions:</strong></p>
  <?php
  $max_sales_this_year = 0;
  $max_sales_all_time = 0;
  $min_sales_this_year = PHP_INT_MAX;
  $min_sales_all_time = PHP_INT_MAX;
  $total_sales = 0;
  foreach ($sales_data_all_time as $data) {
    $total_sales += $data['total_sales'];
    $total_sales_this_year = 0;
    $total_sales_all_time = $data['total_sales'];
    foreach ($sales_data_this_year as $d) {
      if ($d['client'] === $data['client']) {
        $total_sales_this_year = $d['total_sales'];
        break;
      }
    }
    // check if current client has the highest sales this year
    if ($total_sales_this_year > $max_sales_this_year) {
      $max_sales_this_year = $total_sales_this_year;
      $max_sales_this_year_client = $data['client'];
    }
    // check if current client has the highest sales all time
    if ($total_sales_all_time > $max_sales_all_time) {
      $max_sales_all_time = $total_sales_all_time;
      $max_sales_all_time_client = $data['client'];
    }
    // check if current client has the lowest sales this year
    if ($total_sales_this_year < $min_sales_this_year) {
      $min_sales_this_year = $total_sales_this_year;
      $min_sales_this_year_client = $data['client'];
    }
    // check if current client has the lowest sales all time
    if ($total_sales_all_time < $min_sales_all_time) {
      $min_sales_all_time = $total_sales_all_time;
      $min_sales_all_time_client = $data['client'];
    }
  }
  $middle_sales_person = '';
  $middle_sales_diff = PHP_INT_MAX;
  foreach ($sales_data_all_time as $data) {
    $total_sales_this_year = 0;
    $total_sales_all_time = $data['total_sales'];
    foreach ($sales_data_this_year as $d) {
      if ($d['client'] === $data['client']) {
        $total_sales_this_year = $d['total_sales'];
        break;
      }
    }
    $diff = abs($total_sales_all_time - ($total_sales/2));
    if ($diff < $middle_sales_diff) {
      $middle_sales_diff = $diff;
      $middle_sales_person = $data['client'];
      $middle_sales_total = $total_sales_all_time;
    }
  }
  ?>
  <p><?php echo $max_sales_this_year_client; ?> has the highest sales this year with <?php echo number_format($max_sales_this_year, 2); ?> in total sales.</p>
  <p><?php echo $max_sales_all_time_client; ?> has the highest sales of all time with <?php echo number_format($max_sales_all_time, 2); ?> in total sales.</p>
  <p><?php echo $min_sales_this_year_client; ?> has the lowest sales this year with <?php echo number_format($min_sales_this_year, 2); ?> in total sales.</p>
  <p><?php echo $min_sales_all_time_client; ?> has the lowest sales of all time with <?php echo number_format($min_sales_all_time, 2); ?> in total sales.</p>
<p>Rule 1 : To catch up to <?php echo $max_sales_this_year_client; ?>, <?php echo $min_sales_this_year_client; ?> needs to increase their sales by <?php echo number_format($max_sales_this_year - $min_sales_this_year, 2); ?> this year.</p>
<p>Rule 2 : To catch up to <?php echo $max_sales_all_time_client; ?>, <?php echo $min_sales_all_time_client; ?> needs to increase their sales by <?php echo number_format($max_sales_all_time - $min_sales_all_time, 2); ?> in total sales.</p>
<p>Rule 3 : The person who has sold closest to the median amount of <?php echo number_format($total_sales/2, 2); ?> in total sales is <?php echo $middle_sales_person; ?>, with <?php echo number_format($middle_sales_total, 2); ?> in total sales.</p>

  <p><strong>Sales Summary:</strong></p>
  <ul>
    <?php foreach ($sales_data_all_time as $data): ?>
      <li><?php echo $data['client']; ?>: <?php echo number_format($data['total_sales'], 2); ?></li>
    <?php endforeach; ?>
  </ul>

  
</div>
<script>
const toggleTableBtn = document.getElementById('toggle-table-btn');
const salesDataTable = document.getElementById('sales-data-table');
toggleTableBtn.addEventListener('click', function() {
  salesDataTable.classList.toggle('show-table');
});
</script>
