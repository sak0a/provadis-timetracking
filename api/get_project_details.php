<?php
if (file_exists('../backend/database/Database.php') && file_exists('../backend/database/DatabaseUtil.php')) {
    require_once '../backend/database/Database.php';
    require_once '../backend/database/DatabaseUtil.php';
} else if (file_exists('backend/database/Database.php') && file_exists('backend/database/DatabaseUtil.php')) {
    require_once 'backend/database/Database.php';
    require_once 'backend/database/DatabaseUtil.php';
} else {
    die('Required files are missing.');
}

use backend\database\Database;
use backend\database\DatabaseUtil;

// Initialisierung der Datenbankverbindung
$db = Database::initDefault();
$dbUtil = new DatabaseUtil($db->getConnection());

if (isset($_GET['project_id'])) {
    $project_id = intval($_GET['project_id']);
    $employees = $dbUtil->getProjectDetails($project_id);
    echo json_encode($employees);
} else {
    echo json_encode(['error' => 'Project ID is missing']);
}
?>
