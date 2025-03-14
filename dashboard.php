<?php
// A simple dashboard; here you can pull data from the PHP backend and create charts with Chart.js.
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Advanced LG Dashboard</title>
  <link href="<?= LG_CSS_OVERRIDES ?>" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="p-4">
  <h1 class="text-3xl font-bold mb-4">Ağ Test Dashboard</h1>
  <canvas id="networkChart" width="800" height="400"></canvas>
  <script>
    // Sample data; In the next update, data will be retrieved from the backend via AJAX.
    const labels = ['08:00', '09:00', '10:00', '11:00', '12:00'];
    const data = {
      labels: labels,
      datasets: [{
        label: 'Ping (ms)',
        data: [20, 25, 18, 22, 19],
        borderColor: 'rgba(75, 192, 192, 1)',
        fill: false,
      }]
    };
    const config = {
      type: 'line',
      data: data,
    };
    const networkChart = new Chart(
      document.getElementById('networkChart'),
      config
    );
  </script>
</body>
</html>
