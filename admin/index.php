<?php
include('../backend/Auth.php');

session_start();

// Logout-Logik
Auth::logout();
// Login-Logik
Auth::login();
// Session-Logik
Auth::checkSession();

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



/**
 * Content Tab Management START
 */
// If no tab is set, set the default tab to dashboard
if (!isset($_SESSION['admin__current_tab'])) {
    $_SESSION['admin__current_tab'] = 'dashboard';
}
// If a request is made to change the tab, set the new tab
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tab'])) {
    $_SESSION['admin__current_tab'] = $_POST['tab'];
}
$currentTab = $_SESSION['admin__current_tab'];
function loadTab($tab): string {
    if ($tab === 'employees') {
        $content = file_get_contents('employees.php');
        ob_start();
        eval('?>' . $content);
        return ob_get_clean();
    }
    if ($tab === 'projects') {
        $content = file_get_contents('projects.php');
        ob_start();
        eval('?>' . $content);
        return ob_get_clean();
    }
    if ($tab === 'dashboard') {
        $content = file_get_contents('dashboard.php');
        ob_start();
        eval('?>' . $content);
        return ob_get_clean();
    }
    if ($tab === 'statistics') {
        $content = file_get_contents('statistics.php');
        ob_start();
        eval('?>' . $content);
        return ob_get_clean();
    }
    return '';
}
$tabContent = loadTab($currentTab);
// If this is an AJAX request, only return the content part
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax']) && isset($_POST['tab'])) {
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
    <script src="../assets/js/anime.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script src="../assets/js/global.js"></script>
    <link rel="stylesheet" href="../dist/css/style.purged.css">
    <link rel="stylesheet" href="../dist/css/global.css">
    <link rel="stylesheet" href="../dist/css/admin.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
</head>
<body>
<script src="../assets/js/preloader.js"></script>
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
            <header class="admin-top-bar">
                <div class="col-left">
                    <div class="employee-name">
                    <?php if (Auth::isLoggedIn()) {?>
                        <?php echo $benutzerFirstName . ' ' . $benutzerLastName . ' </div><div class="employee-number"> (' . $user['personal_number'] . ')'; ?>
                    <?php } ?>
                    </div>
                </div>
                <div class="col-middle"></div>
                <div class="col-right">
                    <button onclick="window.location='/';" class="employee-view-btn gradient-border">
                        <i class="material-icons">person</i>
                        <span class="ml-1">Mitarbeiteransicht</span>
                    </button>
                    <form method="post" class="admin-top-bar__logout_form">
                        <button type="submit" class="logout-btn anmeldung_form gradient-border" id="logout" name="logout">
                            <i class="material-icons">logout</i>
                            <span class="ml-1">Logout</span>
                        </button>
                    </form>
                    <!-- Dark Mode Toggle Button -->
                    <button id="theme-toggle" class="theme-toggle" type="button" title="Toggle theme" aria-label="Toggle theme">
                        <span class="theme-toggle-sr">Toggle theme</span>
                        <svg
                                xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true"
                                width="1em"
                                height="1em"
                                fill="currentColor"
                                class="theme-toggle__expand"
                                viewBox="0 0 32 32"
                        >
                            <clipPath id="theme-toggle__expand__cutout">
                                <path d="M0-11h25a1 1 0 0017 13v30H0Z" />
                            </clipPath>
                            <g clip-path="url(#theme-toggle__expand__cutout)">
                                <circle cx="16" cy="16" r="8.4" />
                                <path d="M18.3 3.2c0 1.3-1 2.3-2.3 2.3s-2.3-1-2.3-2.3S14.7.9 16 .9s2.3 1 2.3 2.3zm-4.6 25.6c0-1.3 1-2.3 2.3-2.3s2.3 1 2.3 2.3-1 2.3-2.3 2.3-2.3-1-2.3-2.3zm15.1-10.5c-1.3 0-2.3-1-2.3-2.3s1-2.3 2.3-2.3 2.3 1 2.3 2.3-1 2.3-2.3 2.3zM3.2 13.7c1.3 0 2.3 1 2.3 2.3s-1 2.3-2.3 2.3S.9 17.3.9 16s1-2.3 2.3-2.3zm5.8-7C9 7.9 7.9 9 6.7 9S4.4 8 4.4 6.7s1-2.3 2.3-2.3S9 5.4 9 6.7zm16.3 21c-1.3 0-2.3-1-2.3-2.3s1-2.3 2.3-2.3 2.3 1 2.3 2.3-1 2.3-2.3 2.3zm2.4-21c0 1.3-1 2.3-2.3 2.3S23 7.9 23 6.7s1-2.3 2.3-2.3 2.4 1 2.4 2.3zM6.7 23C8 23 9 24 9 25.3s-1 2.3-2.3 2.3-2.3-1-2.3-2.3 1-2.3 2.3-2.3z" />
                            </g>
                        </svg>
                        <!-- SVG source: https://toggles.dev/ -->
                    </button>
                    <!-- Dark Mode Toggle Button -->
                </div>
            </header>
            <!-- partial -->
            <div class="page-wrapper">
                <main id="main">
                    <?php echo $tabContent; ?>
                </main>
            </div>
        </div>
    </div>
<script>
    // Global Veriables
    let currentTab = '<?php echo $currentTab; ?>';
    /**
     * Global Variables for Employee Tab
     */
    let responseData = [];
    let searchInputs;
    let lastRequestTime = 0;
    const throttleDelay = 200;
</script>
    <!-- End custom js for this page-->
</body>

</html>