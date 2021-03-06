<?php

$url_kpi_json = "https://dashboard.swrve.com/api/1/exporter/kpi/avg_playtime.json";
$history = "12";
$granularity = "24hour_rolling";

$data_url = "granularity=$granularity&history=$history";

/*
  
// key moved to include file
 
$api_key = "...";
$personal_key = "...";

 */
include "/var/www/swrve_billionaire_key.php";

$content = exec("curl -G -k \"$url_kpi_json?api_key=$api_key&personal_key=$personal_key&$data_url\"");
$json = json_decode($content);
//var_dump($json);
foreach ($json[0]->data as $k=> $v) {
    $json[0]->data[$k][0] = str_replace("DH-", "", $v[0]);
}

include "/var/www/swrve_billionaire_ios_key.php";

$content_ios = exec("curl -G -k \"$url_kpi_json?api_key=$api_key&personal_key=$personal_key&$data_url\"");
$json_ios = json_decode($content_ios);
//var_dump($json_ios);
foreach ($json_ios[0]->data as $k=> $v) {
    $json_ios[0]->data[$k][0] = str_replace("DH-", "", $v[0]);
}

?>
<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var array = <?php echo json_encode($json[0]->data) ?>;
        array.splice(0, 0, ['Date', 'Total']);
        var data = google.visualization.arrayToDataTable(array);

        var options = {
          title: 'Average Playtime',
          curveType: 'function',
          legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(data, options);
      }
      
      google.charts.setOnLoadCallback(drawChart_ios);

      function drawChart_ios() {
        var array = <?php echo json_encode($json_ios[0]->data) ?>;
        array.splice(0, 0, ['Date', 'Total']);
        var data = google.visualization.arrayToDataTable(array);

        var options = {
          title: 'Average Playtime IOS',
          curveType: 'function',
          legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart_ios'));

        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
    <div id="curve_chart" style="width: 1000px; height: 300px"></div>
    <div id="curve_chart_ios" style="width: 1000px; height: 300px"></div>
  </body>
</html>