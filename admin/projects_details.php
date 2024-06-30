<?php
header('Content-Type: application/json');

require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

try {
    $projectId = isset($_GET['projectId']) ? intval($_GET['projectId']) : 0;

    if ($projectId <= 0) {
        throw new Exception("Invalid project ID");
    }

    $db = Database::initDefault();
    $conn = $db->getConnection();
    $dbUtil = new DatabaseUtil($conn);

    $details = $dbUtil->getProjectDetails($projectId);

    if ($details) {
        echo json_encode($details);
    } else {
        echo json_encode(['error' => 'Project not found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}