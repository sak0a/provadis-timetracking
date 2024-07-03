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
                <a href="javascript:void(0)"><img src="../assets/images/logo.png"
                             alt="logo" class='w-40 mb-8 mx-auto block' />
                </a>
                <div class="form-container">
                    <h2 class="title">Login</h2>
                    <form class="form-element" action="../backend/auth_request.php" method="post">
                        <div class="email-field">
                            <label>E-Mail</label>
                            <div class="input-wrapper">
                                <input name="email" type="email" required class="input-element" placeholder="E-Mail eingeben" />
                                <svg xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" width="20" height="20" class="w-4 h-4 absolute right-4" viewBox="0 0 24 24">
                                    <circle cx="10" cy="7" r="6" data-original="#000000"></circle>
                                    <path d="M14 15H6a5 5 0 0 0-5 5 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 5 5 0 0 0-5-5zm8-4h-2.59l.3-.29a1 1 0 0 0-1.42-1.42l-2 2a1 1 0 0 0 0 1.42l2 2a1 1 0 0 0 1.42 0 1 1 0 0 0 0-1.42l-.3-.29H22a1 1 0 0 0 0-2z" data-original="#000000"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="password-field">
                            <label>Passwort</label>
                            <div class="input-wrapper">
                                <input name="password" type="password" required class="input-element" placeholder="Passwort eingeben" />
                                <svg xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" width="20" height="20" class="w-4 h-4 absolute right-4 cursor-pointer" viewBox="0 0 128 128">
                                    <path d="M64 104C22.127 104 1.367 67.496.504 65.943a4 4 0 0 1 0-3.887C1.367 60.504 22.127 24 64 24s62.633 36.504 63.496 38.057a4 4 0 0 1 0 3.887C126.633 67.496 105.873 104 64 104zM8.707 63.994C13.465 71.205 32.146 96 64 96c31.955 0 50.553-24.775 55.293-31.994C114.535 56.795 95.854 32 64 32 32.045 32 13.447 56.775 8.707 63.994zM64 88c-13.234 0-24-10.766-24-24s10.766-24 24-24 24 10.766 24 24-10.766 24-24 24zm0-40c-8.822 0-16 7.178-16 16s7.178 16 16 16 16-7.178 16-16-7.178-16-16-16z" data-original="#000000"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="bottom-content">
                            <div class="flex items-center">
                                <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 shrink-0 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                                <label for="remember-me" class="ml-3 block text-sm text-gray-800">
                                    Angemeldet bleiben
                                </label>
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