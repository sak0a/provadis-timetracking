
<?php
    $currentTab = "dashboard";


    if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
        $ajaxFunction = $_POST['f'] ?? '';
        if ($ajaxFunction == 'changeTab') {
            $newTab = $_POST['tab'] ?? 'dashboard';
            switch ($newTab) {
                case "dashboard":
                case "statistics":
                case "projects":
                case "employees":
                    $currentTab = $newTab;
                default:
                    $currentTab = "dashboard";
            }
        }
    }



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CommerzBau</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
          rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
</head>
<body>
<script src="../assets/js/preloader.js"></script>




<div class="body-wrapper">
    <!-- partial:../../partials/_sidebar.html -->
<aside class="mdc-drawer mdc-drawer--open">
        <div class="mdc-drawer__header ">
            <img src="../assets/images/logo.png"  width="200" alt="logo" class="pt-4">
        </div>
        <div class="mdc-drawer__content">
            <div class="user-info">
                <p class="name">Laurin Noel Frank</p>
                <!-- TDOO: PHP trim email to n maximum characters -->
                <p class="email">laurinnoel.frank@commer...</p>
            </div>
            <div class="mdc-list-group">
                <nav class="mdc-list mdc-drawer-menu">
                    <div class="mdc-list-item mdc-drawer-item">
                        <a class="mdc-drawer-link" href="">
                            <i class="material-icons mdc-list-item__start-detail mdc-drawer-item-icon" aria-hidden="true">home</i>
                            Dashboard
                        </a>
                    </div>
                    <div class="mdc-list-item mdc-drawer-item">
                        <a class="mdc-drawer-link" href="#projekte">
                            <i class="material-icons mdc-list-item__start-detail mdc-drawer-item-icon" aria-hidden="true">grid_on</i>
                            Projekte
                        </a>
                    </div>
                    <div class="mdc-list-item mdc-drawer-item">
                        <a class="mdc-drawer-link" href="#mitarbeiter">
                            <i class="material-icons mdc-list-item__start-detail mdc-drawer-item-icon" aria-hidden="true">person</i>
                            Mitarbeiter
                        </a>
                    </div>
                    <div class="mdc-list-item mdc-drawer-item">
                        <a class="mdc-drawer-link" href="#statistiken">
                            <i class="material-icons mdc-list-item__start-detail mdc-drawer-item-icon" aria-hidden="true">pie_chart_outlined</i>
                            Statistiken
                        </a>
                    </div>
                </nav>
            </div>
            <div class="profile-actions">
                <a href="/">Mitarbeiteransicht</a>
                <span class="divider"></span>
                <a href="javascript:;">Logout</a>
            </div>

        </div>
    </aside>
    <!-- partial -->
    <div class="main-wrapper mdc-drawer-app-content">
        <!-- partial:../../partials/_navbar.html -->
        <header class="mdc-top-app-bar">
            <div class="mdc-top-app-bar__row">
                <div class="mdc-top-app-bar__section mdc-top-app-bar__section--align-start">
                    <span class="mdc-top-app-bar__title">Willkommen Laurin Noel !</span>
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
                    <button class="mdc-layout-grid__cell mdc-button mdc-button--raised mdc-button--dense filled-button--light text-small mx-auto">
                        <i class="material-icons">logout</i>
                        <span class="ml-1">Logout</span>
                    </button>
                </div>
            </div>
        </header>
        <!-- partial -->
        <div class="page-wrapper mdc-toolbar-fixed-adjust">
            <main class="content-wrapper">
                <?php switch ($currentTab) {
                    case "dashboard": ?>
                        <h1>Dashboard</h1>
                    <?php break;
                    case "statistics": ?>
                        <h1>Statistiken</h1>
                    <?php break;
                    case "projects": ?>
                        <h1>Projekte</h1>
                    <?php break;
                    case "employees": ?>
                        <h1>Mitarbeiter</h1>
                    <?php break; ?>
                <?php } ?>
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
<!-- End custom js for this page-->
</body>
</html>