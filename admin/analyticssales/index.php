<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales and Purchases Nested Heat Map</title>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/heatmap.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <style>
        #heatmap-container {
            width: 1000px; /* Increase width */
            height: 600px; /* Increase height */
            display: none;
        }
        #heatMapChart {
            height: 100%; /* Full height of the container */
            width: 100%; /* Full width of the container */
        }
        #total-sales-purchases {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<button class="button" onclick="toggleHeatMap()">Show Sales and Purchases Heat Map</button>

<div id="total-sales-purchases" style="display: none;">
    <div id="totalSales" style="cursor: pointer;" onclick="createSalesHeatMap()">
        <h3>Total Sales: <span id="totalSalesValue"></span></h3>
    </div>
    <div id="totalPurchases" style="cursor: pointer;" onclick="createPurchaseHeatMap()">
        <h3>Total Purchases: <span id="totalPurchasesValue"></span></h3>
    </div>
</div>

<div id="heatmap-container">
    <div id="heatMapChart"></div>
</div>

<script>
    let salesData = [];
    let purchaseData = [];
    let totalSales = 0;
    let totalPurchases = 0;

    function toggleHeatMap() {
        const container = document.getElementById("heatmap-container");
        const totalsDiv = document.getElementById("total-sales-purchases");

        if (container.style.display === "none") {
            container.style.display = "block";
            totalsDiv.style.display = "block";
            showHeatMap();
        } else {
            container.style.display = "none";
            totalsDiv.style.display = "none";
        }
    }

    function showHeatMap() {
        // Fetch sales and purchase data from PHP
        salesData = <?php
            $result_this_month = $conn->query("SELECT DATE_FORMAT(date_created, '%Y-%m-%d') as sales_date, SUM(amount) as total_sales 
                FROM sales_list 
                WHERE MONTH(date_created) BETWEEN 1 AND MONTH(CURRENT_DATE())
                AND DATE(date_created) <= CURRENT_DATE()
                GROUP BY DATE(date_created) 
                ORDER BY DATE(date_created) ASC");
            $sales_data_this_month = array();
            while ($row = $result_this_month->fetch_assoc()) {
                $sales_data_this_month[] = $row;
            }
            echo json_encode($sales_data_this_month);
        ?>;

        purchaseData = <?php
            $result_this_month = $conn->query("SELECT DATE_FORMAT(date_created, '%Y-%m-%d') as purchase_date, SUM(amount) as total_purchase 
                FROM purchase_order_list 
                WHERE MONTH(date_created) BETWEEN 1 AND MONTH(CURRENT_DATE())
                AND DATE(date_created) <= CURRENT_DATE()
                GROUP BY DATE(date_created) 
                ORDER BY DATE(date_created) ASC");
            $purchase_data_this_month = array();
            while ($row = $result_this_month->fetch_assoc()) {
                $purchase_data_this_month[] = $row;
            }
            echo json_encode($purchase_data_this_month);
        ?>;

        // Calculate total sales and purchases
        totalSales = salesData.reduce((sum, sale) => sum + parseFloat(sale.total_sales), 0);
        totalPurchases = purchaseData.reduce((sum, purchase) => sum + parseFloat(purchase.total_purchase), 0);

        // Display total sales and purchases
        document.getElementById("totalSalesValue").textContent = totalSales.toFixed(2);
        document.getElementById("totalPurchasesValue").textContent = totalPurchases.toFixed(2);
        
        // Create combined heat map for initial view
        createCombinedHeatMap();
    }

    function createCombinedHeatMap() {
        const data = [];

        // Add total sales to the data array
        data.push([0, 0, totalSales]); // Total Sales
        // Add total purchases to the data array
        data.push([0, 1, totalPurchases]); // Total Purchases

        // Create the heatmap
        Highcharts.chart('heatMapChart', {
            chart: {
                type: 'heatmap',
                plotBorderWidth: 1,
                marginTop: 40,
                marginBottom: 40,
                events: {
                    click: function(event) {
                        const point = this.series[0].searchPoint(event);
                        if (point) {
                            if (point.y === 0) {
                                createSalesHeatMap();
                            } else if (point.y === 1) {
                                createPurchaseHeatMap();
                            }
                        }
                    }
                }
            },
            title: {
                text: 'Sales and Purchases Combined Heat Map'
            },
            xAxis: {
                categories: ['Total'], // Show 'Total' for the first category
            },
            yAxis: {
                categories: ['Total Sales', 'Total Purchases'],
                title: null
            },
            colorAxis: {
                min: 0,
                minColor: '#FFFFFF',
                maxColor: Highcharts.getOptions().colors[0],
            },
            tooltip: {
                formatter: function () {
                    return `<b>${this.series.yAxis.categories[this.point.y]}:</b> ${this.point.value}`;
                }
            },
            series: [{
                name: 'Sales and Purchases Data',
                borderWidth: 1,
                data: data,
                dataLabels: {
                    enabled: true,
                    color: '#000000'
                }
            }]
        });
    }

    function createSalesHeatMap() {
        const salesOnlyData = salesData.map((sale, index) => [index, 0, parseFloat(sale.total_sales)]);

        Highcharts.chart('heatMapChart', {
            chart: {
                type: 'heatmap',
                plotBorderWidth: 1,
            },
            title: {
                text: 'Sales Heat Map'
            },
            xAxis: {
                categories: salesData.map(sale => sale.sales_date),
                title: {
                    text: 'Date'
                }
            },
            yAxis: {
                categories: ['Total Sales'],
                title: null
            },
            colorAxis: {
                min: 0,
                minColor: '#FFFFFF',
                maxColor: Highcharts.getOptions().colors[0],
            },
            tooltip: {
                formatter: function () {
                    return `<b>Sales:</b> ${this.point.value}`;
                }
            },
            series: [{
                name: 'Sales Data',
                borderWidth: 1,
                data: salesOnlyData,
                dataLabels: {
                    enabled: true,
                    color: '#000000'
                }
            }]
        });
    }

    function createPurchaseHeatMap() {
        const purchaseOnlyData = purchaseData.map((purchase, index) => [index, 0, parseFloat(purchase.total_purchase)]);

        Highcharts.chart('heatMapChart', {
            chart: {
                type: 'heatmap',
                plotBorderWidth: 1,
            },
            title: {
                text: 'Purchases Heat Map'
            },
            xAxis: {
                categories: purchaseData.map(purchase => purchase.purchase_date),
                title: {
                    text: 'Date'
                }
            },
            yAxis: {
                categories: ['Total Purchases'],
                title: null
            },
            colorAxis: {
                min: 0,
                minColor: '#FFFFFF',
                maxColor: Highcharts.getOptions().colors[0],
            },
            tooltip: {
                formatter: function () {
                    return `<b>Purchases:</b> ${this.point.value}`;
                }
            },
            series: [{
                name: 'Purchase Data',
                borderWidth: 1,
                data: purchaseOnlyData,
                dataLabels: {
                    enabled: true,
                    color: '#000000'
                }
            }]
        });
    }
</script>

</body>
</html>
