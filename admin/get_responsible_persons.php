<?php
header('Content-Type: application/json');
require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

try {
    $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

    if ($projectId <= 0) {
        throw new Exception("Invalid project ID");
    }

    $db = Database::initDefault();
    $dbUtil = new DatabaseUtil($db->getConnection());

    $responsiblePersons = $dbUtil->getResponsiblePersons($projectId);

    if ($responsiblePersons) {
        echo json_encode($responsiblePersons);
    } else {
        echo json_encode(['error' => 'No responsible persons found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}