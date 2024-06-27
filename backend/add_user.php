<?php
ob_start();
session_start();

require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/database/DatabaseUtil.php';


use backend\database\Database;
use backend\database\DatabaseUtil;


// Initialisierung der Datenbankverbindung
$db = Database::initDefault();
// Erstellen eines DatabaseUtil-Objekts
$dbUtil = new DatabaseUtil($db->getConnection());

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $personal_number = $_POST['personal_number'];
    $email= $_POST['email'];
    $firstName= $_POST['first_name'];
    $lastName= $_POST['last_name'];
    $birthdate= $_POST['birthdate'];
    $password= $_POST['password'];
    $role= $_POST['role_id'];
    $entry_date= $_POST['entry_date'];

    // Überprüfen, ob der Benutzer existiert
    if ($dbUtil->userExists($email)) {
        // Benutzer abrufen
        echo'Der user ist schon existiert';
        exit();

    } else {
        if ($dbUtil->createUser($personal_number, $email, $firstName, $lastName, $birthdate, $password, $role, $entry_date)) {
            echo 'Der Benutzer wurde erfolgreich angelegt';
            header("Location: ../admin");
        } else {
            echo 'Fehler beim Anlegen des Benutzers';
        }
        exit();
    }
}

// Schließen der Datenbankverbindung
$db->__destruct();

ob_end_flush();
exit();
?>