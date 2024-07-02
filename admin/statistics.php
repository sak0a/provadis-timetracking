<?php
if (strpos($_SERVER['REQUEST_URI'], 'admin/statistics.php') !== false) {
    echo $_SERVER['REQUEST_URI'];
    header("Location: ../admin");
    exit();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CommerzBau</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
    <script src="../assets/js/chartjs.js"></script>
</head>
<body>

<div class="container">
    
    <!-- Benutzerverwaltung -->
    <div class="section">
        <h2>Statistik</h2>
    </div>

    <div class="page-wrapper mdc-toolbar-fixed-adjust">
        <main class="content-wrapper">
          <div class="mdc-layout-grid">
            <div class="mdc-layout-grid__inner">
              <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                <div class="mdc-card">
                  <h6 class="card-title">Line chart</h6>
                  <canvas id="lineChart"></canvas>
                </div>
              </div>
              <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                <div class="mdc-card">
                  <h6 class="card-title">Bar chart</h6>
                  <canvas id="barChart"></canvas>
                </div>
              </div>
              <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                <div class="mdc-card">
                  <h6 class="card-title">Area chart</h6>
                  <canvas id="areaChart"></canvas>
                </div>
              </div>
              <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                <div class="mdc-card">
                  <h6 class="card-title">Doughnut chart</h6>
                  <canvas id="doughnutChart"></canvas>
                </div>
              </div>
              <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                <div class="mdc-card">
                  <h6 class="card-title">Pie chart</h6>
                  <canvas id="pieChart"></canvas>
                </div>
              </div>
              <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                <div class="mdc-card">
                  <h6 class="card-title">Scatter chart</h6>
                  <canvas id="scatterChart"></canvas>
                </div>
              </div>
            </div>
          </div>
        </main>
        <!-- partial:../../partials/_footer.html -->
        <footer>
          <div class="mdc-layout-grid">
            <div class="mdc-layout-grid__inner">
              <div class="mdc-layout-grid__cell stretch-card mdc-layout-grid__cell--span-6-desktop">
                <span class="text-center text-sm-left d-block d-sm-inline-block tx-14">Copyright © <a href="https://www.bootstrapdash.com/" target="_blank">bootstrapdash.com </a>2020</span>
              </div>
              <div class="mdc-layout-grid__cell stretch-card mdc-layout-grid__cell--span-6-desktop d-flex justify-content-end">
                <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center tx-14">Free <a href="https://www.bootstrapdash.com/material-design-dashboard/" target="_blank"> material admin </a> dashboards from Bootstrapdash.com</span>
              </div>
            </div>
          </div>
        </footer>
        <!-- partial -->
  <!-- plugins:js -->
  <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page-->
  <script src="../assets/vendors/chartjs/Chart.min.js"></script>
  <!-- End plugin js for this page-->
  <!-- inject:js -->

  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="../../assets/js/chartjs.js"></script>
  <!-- End custom js for this page-->
</body>
</html>
