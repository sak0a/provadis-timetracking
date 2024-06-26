<?php
use vendor\database\Database;
include("../backend/database/Database.php");
session_start();

if (!isset($_SESSION['angemeldet']) || $_SESSION['angemeldet'] !== true) {
    header("Location: ../login");
    exit();
}

$currentTab = "dashboard";
$benutzerFirstName = htmlspecialchars($_SESSION['first_name']);
$benutzerLastName = htmlspecialchars($_SESSION['last_name']);
$benutzerEmail = htmlspecialchars($_SESSION['email']);
$benutzerRole = htmlspecialchars($_SESSION['role']);

// Logout-Logik
if (isset($_POST['logout'])) {
    unset($_SESSION['angemeldet']);
    unset($_SESSION['username']);
    if (isset($_COOKIE['secure_user'])) {
        setcookie('secure_user', '', time() - 3600 * 24 * 7, '/');
        unset($_COOKIE['secure_user']);
    }
    session_regenerate_id(true);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['login']) && !empty($_POST['username'])) {
    $_SESSION['angemeldet'] = true;
    $_SESSION['username'] = $_POST['username'];
    session_regenerate_id(true);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
if (isset($_COOKIE['secure_user']) && $_SESSION['angemeldet'] !== true) {
    require_once 'crypt.php';
    if (function_exists('decryptCookie')) {
        $userId = decryptCookie($_COOKIE['secure_user']);
        if ($userId) {
            $_SESSION['angemeldet'] = true;
            $_SESSION['username'] = $userId;
            session_regenerate_id(true);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

if (!isset($_SESSION['admin__current_tab'])) {
    $_SESSION['admin__current_tab'] = 'dashboard';
}
// Update tab if a POST request is made
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tab'])) {
    $_SESSION['admin__current_tab'] = $_POST['tab'];
}
$currentTab = $_SESSION['admin__current_tab'];
function loadTab($tab) {
    return match ($tab) {
        'employees' => '<h1>Employees Content</h1>',
        'projects' => '<h1>Projects Content</h1>',
        'statistics' =>
        '<div class="mdc-layout-grid">
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
          </div>',
        default => '<h1>Dashboard Content</h1>',
    };
}
$tabContent = loadTab($currentTab);
// If this is an AJAX request, only return the content part
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    echo $tabContent;
    exit;
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
</head>

<body>
    <script src="../assets/js/preloader.js"></script>
    <script src="../assets/js/admin.js"></script>



    <div class="body-wrapper">
        <!-- partial:../../partials/_sidebar.html -->
        <aside class="mdc-drawer mdc-drawer--open">
            <div class="mdc-drawer__header ">
                <img src="../assets/images/logo.png" width="200" alt="logo" class="pt-4">
            </div>
            <div class="mdc-drawer__content">
                <div class="user-info">
                    <?php if (isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true) {
                    ?>
                        <div id="angemeldet_als"><?php echo $benutzerFirstName . ' ' . $benutzerLastName; ?></div>
                    <?php } else { ?>
                    <?php } ?>
                    <!-- TDOO: PHP trim email to n maximum characters -->
                    <p class="email"><?php if (isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true) {
                                        ?>
                    <div id="angemeldet_als"><?php echo $benutzerEmail; ?></div><?php } else { ?>
                <?php } ?>
                </p>
                </div>
                <div class="mdc-list-group">
                    <nav class="mdc-list mdc-drawer-menu">
                        <div class="mdc-list-item mdc-drawer-item">
                        <a class="mdc-drawer-link" onclick="switchContentTo('dashboard')">
                                <i class="material-icons mdc-list-item__start-detail mdc-drawer-item-icon" aria-hidden="true">home</i>
                                Dashboard
                            </a>
                        </div>
                        <div class="mdc-list-item mdc-drawer-item">
                        <a class="mdc-drawer-link" onclick="switchContentTo('projects')">
                                <i class="material-icons mdc-list-item__start-detail mdc-drawer-item-icon" aria-hidden="true">grid_on</i>
                                Projekte
                            </a>
                        </div>
                        <div class="mdc-list-item mdc-drawer-item">
                        <a class="mdc-drawer-link" onclick="switchContentTo('employees')">
                                <i class="material-icons mdc-list-item__start-detail mdc-drawer-item-icon" aria-hidden="true">person</i>
                                Mitarbeiter
                            </a>
                        </div>
                        <div class="mdc-list-item mdc-drawer-item">
                        <a class="mdc-drawer-link" onclick="switchContentTo('statistics')">
                                <i class="material-icons mdc-list-item__start-detail mdc-drawer-item-icon" aria-hidden="true">pie_chart_outlined</i>
                                Statistiken
                            </a>
                        </div>
                    </nav>
                </div>
                <div class="profile-actions">
                    <a href="/">Mitarbeiteransicht</a>
                    <span class="divider"></span>
                    <form method="post">
                        <a type="submit" class="anmeldung_form" id="logout" name="logout" onclick="location.href='login.php'">Abmelden</a>
                    </form>
                </div>

            </div>
        </aside>
        <!-- partial -->
        <div class="main-wrapper mdc-drawer-app-content">
            <!-- partial:../../partials/_navbar.html -->
            <header class="mdc-top-app-bar">
                <div class="mdc-top-app-bar__row">
                    <div class="mdc-top-app-bar__section mdc-top-app-bar__section--align-start">
                        <span class="mdc-top-app-bar__title">
                            <div id="angemeldet_als">

                                <?php if (isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true) {
                                ?>
                                    <div id="angemeldet_als"><?php echo $benutzerFirstName . ' ' . $benutzerLastName; ?></div>
                                <?php } else { ?>
                                <?php } ?>

                            </div>
                        </span>
                        <div class="mdc-text-field mdc-text-field--outlined mdc-text-field--with-leading-icon search-text-field d-none d-md-flex">
                            <i class="material-icons mdc-text-field__icon">search</i>
                            <input class="mdc-text-field__input" id="text-field-hero-input">
                            <div class="mdc-notched-outline">
                                <div class="mdc-notched-outline__leading"></div>
                                <div class="mdc-notched-outline__notch">
                                    <label for="text-field-hero-input" class="mdc-floating-label">Search..</label>
                                </div>
                                <div class="mdc-notched-outline__trailing"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mdc-top-app-bar__section mdc-top-app-bar__section--align-end mdc-top-app-bar__section-right">

                        <button onclick="window.location='/';" class="mdc-layout-grid__cell mdc-button mdc-button--raised mdc-button--dense filled-button--light text-small mx-auto">
                            <i class="material-icons">person</i>
                            <span class="ml-1">Mitarbeiteransicht</span>
                        </button>
                        <span class="divider"></span>
                        <form method="post" style="display: inline;">
                            <button type="submit" class="mdc-layout-grid__cell mdc-button mdc-button--raised mdc-button--dense filled-button--light text-small mx-auto anmeldung_form" id="logout" name="logout">
                                <i class="material-icons">logout</i>
                                <span class="ml-1">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>
            <!-- partial -->
            <div class="page-wrapper mdc-toolbar-fixed-adjust">
            <main class="content-wrapper" id="main">
                <?php echo $tabContent; ?>
            </main>
            </div>
        </div>
    </div>





    <!-- plugins:js -->
    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page-->
    <!-- End plugin js for this page-->
    <!-- inject:js -->
    <script src="../assets/js/material.js"></script>
    <script src="../assets/js/misc.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
<script>
    var currentTab = "<?php echo $currentTab; ?>";
</script>
    <!-- End custom js for this page-->
</body>

</html>