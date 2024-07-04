<?php
header('Content-Type: application/json');
require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

session_start();

try {
    $db = Database::initDefault();
    $dbUtil = new DatabaseUtil($db->getConnection());

    $projectDetails = $dbUtil->getAllProjectsForStatistic();

    if ($projectDetails) {
        echo json_encode($projectDetails);
    } else {
        echo json_encode(['error' => 'No projects found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
