<?php
include('../backend/Auth.php');

session_start();

if (Auth::isLoggedIn()) {
    header("Location: ../");
    exit();
}

// Logout-Logik
Auth::Logout();
// Login-Logik
Auth::Login();
// Session-Logik
Auth::CheckSession();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kommen-Gehen Log-In</title>
    <!-- plugins:css -->
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
</head>
<body>
<script src="../assets/js/preloader.js"></script>
<div class="body-wrapper">
    <div class="main-wrapper">
        <div class="page-wrapper full-page-wrapper d-flex align-items-center justify-content-center">
            <main class="auth-page">
                <div class="mdc-layout-grid">
                    <div class="mdc-layout-grid__inner">
                        <div class="stretch-card mdc-layout-grid__cell--span-4-desktop mdc-layout-grid__cell--span-1-tablet"></div>
                        <div class="mdc-layout-grid__cell stretch-card mdc-layout-grid__cell--span-4-desktop mdc-layout-grid__cell--span-6-tablet">
                            <div class="mdc-card">
                                <form action="../backend/auth_request.php" method="post">
                                    <div class="mdc-layout-grid">
                                        <div class="mdc-layout-grid__inner">
                                            <div class="mdc-layout-grid__cell stretch-card mdc-layout-grid__cell--span-12">
                                                <div class="mdc-text-field w-100">

                                                    <input class="mdc-text-field__input" id="text-field-hero-input" type="email" name="email" required>
                                                    <div class="mdc-line-ripple"></div>
                                                    <label for="text-field-hero-input" class="mdc-floating-label">Email</label>
                                                </div>
                                            </div>
                                            <div class="mdc-layout-grid__cell stretch-card mdc-layout-grid__cell--span-12">
                                                <div class="mdc-text-field w-100">
                                                    <input class="mdc-text-field__input" type="password" id="text-field-hero-input" name="password" required>
                                                    <div class="mdc-line-ripple"></div>
                                                    <label for="text-field-hero-input" class="mdc-floating-label">Passwort</label>
                                                </div>
                                            </div>
                                            <div class="mdc-layout-grid__cell stretch-card mdc-layout-grid__cell--span-6-desktop">

                                            </div>
                                            <div class="mdc-layout-grid__cell stretch-card mdc-layout-grid__cell--span-6-desktop d-flex align-items-center justify-content-end">
                                                <a href="#">Passwort vergessen</a>
                                            </div>
                                            <div class="mdc-layout-grid__cell stretch-card mdc-layout-grid__cell--span-12">
                                                <button class="mdc-button mdc-button--raised w-100" type="submit">
                                                    Login
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="stretch-card mdc-layout-grid__cell--span-4-desktop mdc-layout-grid__cell--span-1-tablet"></div>
                    </div>
                </div>
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
<!-- <script src="../assets/js/material.js"></script>
<script src="../assets/js/misc.js"></script> -->
<span id="loginBtn" style="box-shadow: 0px 0px 0px;"></span>
<span id="registerBtn" style="box-shadow: 0px 0px 0px;"></span>
<!-- endinject -->
<!-- Custom js for this page-->
<!-- End custom js for this page-->
</body>
</html>