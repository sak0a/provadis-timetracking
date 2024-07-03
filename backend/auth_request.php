<?php
ob_start();
session_start();

require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/database/DatabaseUtil.php';
require_once __DIR__ . '/Crypt.php';
require_once __DIR__ . '/Auth.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

// Initialize the database connection
$db = Database::initDefault();
$dbUtil = new DatabaseUtil($db->getConnection());

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the user exists
    if ($dbUtil->userExists($email)) {
        // Retrieve user data
        $user = $dbUtil->getUserByEmail($email);

        if (password_verify($password, $user['password_hash'])) {
            // Start session and set user data
            $_SESSION[Auth::SESSION_AUTH_KEY] = true;
            $_SESSION[Auth::SESSION_EMAIL_KEY] = $email;
            $_SESSION['user'] = $user;
            session_regenerate_id(true);


            // Set secure cookie
            Auth::setSecureCookie($email);

            header("Location: ../");
            exit();
        } else {
            $_SESSION['error'] = "Passwort oder E-Mail ist falsch.";
            header("Location: ../login");
            exit();
        }
    } else {
        $_SESSION['error'] = "Passwort oder E-Mail ist falsch.";
        header("Location: ../login");
        exit();
    }
}

// Close the database connection
$db->__destruct();

ob_end_flush();
exit();
?>