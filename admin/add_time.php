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
    $user_id = $_POST['user_id'];
    $project_id = $_POST['project2'];
    $task_id= $_POST['tasks2'];
    $datePicker= $_POST['datePicker'];
    $start_time= $_POST['datePicker'] ." ". $_POST['startTime'];
    $end_time= $_POST['datePicker'] ." ". $_POST['endTime'];
    $approved_by = $_POST['approved_by'];
    //$department_head= $_POST['department_head'];

    echo $user_id ." ". $project_id ." ". $task_id ." ". $datePicker ." ". $start_time ." ". $end_time ." ". $approved_by;
    // Überprüfen, ob der Benutzer existiert
        if ($dbUtil->createTimeEntry($user_id, $project_id, $task_id, $start_time, $end_time, $approved_by)) {
            echo 'Der project wurde erfolgreich angelegt';
            header("Location: ../admin");
        } else {
            echo 'Fehler beim Anlegen des projects';
        }
        exit();
      }

// Schließen der Datenbankverbindung
$db->__destruct();

ob_end_flush();
exit();
?>