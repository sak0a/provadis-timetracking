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
    $project_name = $_POST['project2'];
    $task_name= $_POST['tasks2'];
    $datePicker= $_POST['datePicker'];
    $start_time= $_POST['datePicker'] ." ". $_POST['startTime'];
    $end_time= $_POST['datePicker'] ." ". $_POST['endTime'];
    $approved_by = $_POST['approved_by'];
    //$department_head= $_POST['department_head'];

    echo $user_id ." ". $project_name ." ". $task_name ." ". $datePicker ." ". $start_time ." ". $end_time ." ". $approved_by;
    // Überprüfen, ob der Benutzer existiert
    if ($dbUtil->createTableEntry($user_id, $project_name, $task_name, $start_time, $end_time, $approved_by)) {
        //echo "<script>alert('Die Zeiten sind erfolgreich gespeichert');window.location.href='../admin';</script>";
        header("Location: ../admin");
    } else {
        echo "<script>alert('Es gibt Zeitüberschneidung');window.location.href='../admin';</script>";
    }
}

ob_end_flush();
exit();
?>