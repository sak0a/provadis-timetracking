<?php
ob_start();
session_start();

require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/database/DatabaseUtil.php';
require_once __DIR__ . '/crypt.php';


use backend\database\Database;
use backend\database\DatabaseUtil;


// Initialisierung der Datenbankverbindung
$db = Database::initDefault();
// Erstellen eines DatabaseUtil-Objekts
$dbUtil = new DatabaseUtil($db->getConnection());

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Überprüfen, ob der Benutzer existiert
    if ($dbUtil->userExists($email)) {
        // Benutzer abrufen
        $user = $dbUtil->getUserByEmail($email);

        if (password_verify($password, $user['password_hash'])) {
            // Sitzung starten und Benutzerdaten setzen
            $_SESSION['angemeldet'] = true;
            $_SESSION['user'] = $user;
            session_regenerate_id(true);

            setcookie('angemeldet', true, time() + 3600 * 24 * 7, '/', '', true, true);
            $encryptedCookie = encryptCookie($email);
            setcookie('secure_user', $encryptedCookie, time() + 3600 * 24 * 7, '/', '', true, true);

            header("Location: ../");
            echo 'alles geklappt';
            exit();
        } else {
            $_SESSION['error'] = "Falsches Passwort.";
            header("Location: ../login");
            echo 'Falsches Passwort';
            exit();
        }
    } else {
        $_SESSION['error'] = "Benutzer nicht gefunden.";
        //header("Location: ../login");
        echo 'Benutzer nicht gefunden.';
        exit();
    }
}

// Schließen der Datenbankverbindung
$db->__destruct();

ob_end_flush();
exit();
?>