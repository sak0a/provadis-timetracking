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
  <link rel="stylesheet" href="../dist/css/style.purged.css">
    <link rel="stylesheet" href="../dist/css/global.css">
    <link rel="stylesheet" href="../dist/css/admin.css">
  <link rel="shortcut icon" href="../assets/images/favicon.png" />
 
     
</head>

<body>
<div class="projects-container">
    <!-- Benutzerübersicht -->
    <div class="projects-main-container">
        <!-- Container Header -->
        <!-- <div class="mdc-layout-grid__inner"> -->
        <div class="statistic_container" id="projectDetailsContent">
        </div>
      <!-- </div> -->
    </div>
</div>

   
</body>
</html>