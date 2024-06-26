<?php
ob_start();
session_start();

require_once __DIR__ . '/../backend/database/Database.php';
require_once __DIR__ . '/../backend/database/DatabaseUtil.php';
require_once __DIR__ . '/../backend/crypt.php';


use backend\database\Database;
use backend\database\DatabaseUtil;


// Initialisierung der Datenbankverbindung
$db = new Database("korra.design", "provadis", "alexandros2406", "provadis_project");
$conn = $db->getConnection();

// Erstellen eines DatabaseUtil-Objekts
$dbUtil = new DatabaseUtil($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Überprüfen, ob der Benutzer existiert
    if ($dbUtil->userExists($username)) {
        // Benutzer abrufen
        $user = $dbUtil->userExists($username);
        if (password_verify($password, $user['password_hash'])) {
            // Sitzung starten und Benutzerdaten setzen
            $_SESSION['angemeldet'] = true;            
            $_SESSION['username'] = $username;
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name']; 
            $_SESSION['role'] = $user['role']; 
            $_SESSION['email'] = $user['email']; 
            session_regenerate_id(true);

            setcookie('angemeldet', true, time() + 3600 * 24 * 7, '/', '', true, true);
            $encryptedCookie = encryptCookie($username);
            setcookie('secure_user', $encryptedCookie, time() + 3600 * 24 * 7, '/', '', true, true);

            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Falsches Passwort.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Benutzer nicht gefunden.";
        header("Location: login.php");
        exit();
    }
}

// Schließen der Datenbankverbindung
$db->__destruct();

ob_end_flush();
exit();
?>
