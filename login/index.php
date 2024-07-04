<?php
include('../backend/Auth.php');

session_start();

// Logout-Logik
Auth::logout();
// Login-Logik
Auth::login();
// Session-Logik
Auth::checkSession();

if (Auth::isLoggedIn()) {
    header("Location: ../");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kommen-Gehen Log-In</title>
    <!-- Layout styles -->
    <link rel="stylesheet" href="../dist/css/global.css">
    <link rel="stylesheet" href="../dist/css/login.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
</head>
<body class="">

<div class="body-wrapper ">

    <div class="">
        <div class="page-wrapper">
            <div class="form-wrapper">
                <a href="javascript:void(0)"><img src="../assets/images/logo-yellow.png"
                             alt="logo" class='w-40 mb-8 mx-auto block' />
                </a>
                <div class="form-container">
                    <h2 class="title">Login</h2>
                    <form class="form-element" action="../backend/auth_request.php" method="post">
                        <div class="email-field">
                            <label>E-Mail</label>
                            <div class="input-wrapper">
                                <input name="email" type="email" required class="input-element" placeholder="E-Mail eingeben" />
                            </div>
                        </div>

                        <div class="password-field">
                            <label>Passwort</label>
                            <div class="input-wrapper">
                                <input name="password" type="password" required class="input-element" placeholder="Passwort eingeben" />
                            </div>
                        </div>

                        <div class="bottom-content">
                            <div class="flex items-center">
                            </div>
                            <div class="text-sm">
                                <a href="/password-reset?" class="text-secondary hover:underline font-semibold">
                                    Password vergessen?
                                </a>
                            </div>
                        </div>

                        <div class="!mt-8">
                            <button type="submit" class="login-button gradient-border">
                                Login
                            </button>
                        </div>
                        <?php
                            if (isset($_SESSION['error'])) {
                                echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
                                unset($_SESSION['error']);
                            }
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../assets/vendors/js/vendor.bundle.base.js"></script>
</body>
</html>