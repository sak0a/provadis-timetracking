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
    $project_name = $_POST['project_name'];
    $start_date= $_POST['start_date'];
    $status_id= $_POST['status_id'];
    //$department_head= $_POST['department_head'];

    // Überprüfen, ob der Benutzer existiert
    if ($dbUtil->projectExists($project_name)) {
        // Benutzer abrufen
        echo'Der project ist schon existiert';
        exit();

    } else {
        if ($dbUtil->createProject($project_name, $start_date, $status_id)) {
            echo 'Der project wurde erfolgreich angelegt';
            header("Location: ../admin");
        } else {
            echo 'Fehler beim Anlegen des projects';
        }
        exit();
    }
}

// Schließen der Datenbankverbindung
$db->__destruct();

ob_end_flush();
exit();
?>