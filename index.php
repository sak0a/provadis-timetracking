
<?php
include('backend/Auth.php');

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Server Monitoring</title>
</head>
<body class="">
<h1>
    Main Page
</h1>
</body>
</html>