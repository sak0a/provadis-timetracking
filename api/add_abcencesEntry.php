<?php
ob_start();
session_start();

require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

// Initialisierung der Datenbankverbindung
$db = Database::initDefault();
// Erstellen eines DatabaseUtil-Objekts
$dbUtil = new DatabaseUtil($db->getConnection());

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_idAbs'];
    $artAbsences = $_POST['artAbsences'];
    $datePickerAbsencesStart = $_POST['datePickerAbsencesStart'];
    $datePickerAbsencesEnd = $_POST['datePickerAbsencesEnd'];

    // Überprüfen, ob das Enddatum größer oder gleich dem Startdatum ist
    if (strtotime($datePickerAbsencesEnd) >= strtotime($datePickerAbsencesStart)) {
        // Überprüfen, ob der Benutzer existiert und Eintrag in der Datenbank erstellen
        if ($dbUtil->createAbsencesEntry($user_id, $artAbsences, $datePickerAbsencesStart, $datePickerAbsencesEnd)) {
            echo 'Der Abwesenheitseintrag wurde erfolgreich angelegt';
            header("Location: /");
        } else {
            echo 'Fehler beim Anlegen des Abwesenheitseintrags';
        }
    } else {
        echo 'Das Enddatum muss größer oder gleich dem Startdatum sein';
        echo "<script>alert('Das Enddatum muss größer oder gleich dem Startdatum sein');</script>";
        header("Location: /");
    }
    exit();
}

// Schließen der Datenbankverbindung
$db->__destruct();

ob_end_flush();
exit();
?>
