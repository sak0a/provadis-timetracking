<?php
include '../backend/database/Database.php';
include '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

$db = Database::initDefault();
$dbUtil = new DatabaseUtil($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['personal_number'])) {
    $personalNumber = intval($_GET['personal_number']);
    $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : null;
    $userDetails = $dbUtil->getUserDetails($personalNumber, $projectId);
    echo json_encode($userDetails);
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
