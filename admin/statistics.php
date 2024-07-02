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

    <div class="section">
      <h2>Statistik</h2>
    </div>



    <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
      <div class="mdc-card">
        <h6 class="card-title">Pie chart</h6>
        <canvas id="pieChart"></canvas>

      </div>
    </div>

  </div>
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