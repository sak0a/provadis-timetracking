<?php
include('../backend/Auth.php');
include("../backend/database/Database.php");
session_start();


Auth::CheckSession();

if (!Auth::isLoggedIn()) {
    header("Location: ../login");
    exit();
}

$currentTab = "dashboard";

$user = $_SESSION['user'];
$benutzerFirstName = htmlspecialchars($user['first_name']);
$benutzerLastName = htmlspecialchars($user['last_name']);
$benutzerEmail = htmlspecialchars($user['email']);
$benutzerRole = htmlspecialchars($user['role_id']);
if ($benutzerRole==='1'){$benutzerRole='Projektverantwortlicher';}
else{$benutzerRole= 'Mitarbeiter';}



// Logout-Logik
Auth::Logout();
// Login-Logik
Auth::Login();

if (!isset($_SESSION['admin__current_tab'])) {
    $_SESSION['admin__current_tab'] = 'dashboard';
}
// Update tab if a POST request is made
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tab'])) {
    $_SESSION['admin__current_tab'] = $_POST['tab'];
}
$currentTab = $_SESSION['admin__current_tab'];
function loadTab($tab): string{

    if ($tab === 'employees') {
        $content = file_get_contents('employees.php');
        ob_start();
        eval('?>' . $content);
        $content = ob_get_clean();
        return $content;
    }
    if ($tab === 'projects') {
        $content = file_get_contents('projects.php');
        ob_start();
        eval('?>' . $content);
        $content = ob_get_clean();
        return $content;
    }
    if ($tab === 'dashboard') {
        $content = file_get_contents('dashboard.php');
        ob_start();
        eval('?>' . $content);
        $content = ob_get_clean();
        return $content;
    }
    if ($tab === 'statistics') {
        $content = file_get_contents('statistics.php');
        ob_start();
        eval('?>' . $content);
        $content = ob_get_clean();
        return $content;
    }
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
                        <p><?php echo $benutzerFirstName . ' ' . $benutzerLastName; ?></p>
                        <p><?php echo $benutzerEmail; ?></p>
                        <p><?php echo $benutzerRole; ?></p>

                    <?php } else { ?>
                    <?php } ?>
                    <!-- TDOO: PHP trim email to n maximum characters -->
                    
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
                        <button type="submit" class="anmeldung_form" id="logout" name="logout">
                            Abmelden
                        </button>
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
                        <?php if (isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true) {
                                ?>
                                    <p><?php echo $benutzerFirstName . ' ' . $benutzerLastName; ?></p>
                                <?php } else { ?>
                                <?php } ?>
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
    <script src="../assets/vendors/chartjs/Chart.min.js"></script>
    <!-- End plugin js for this page-->
    <!-- inject:js -->
    <script src="../assets/js/material.js"></script>
    <script src="../assets/js/misc.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
<script>
    let currentTab = "<?php echo $currentTab; ?>";
</script>
    <!-- End custom js for this page-->
</body>

</html>