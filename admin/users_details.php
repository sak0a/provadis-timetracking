<?php
header('Content-Type: application/json');

require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

try {
    $userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;

    if ($userId <= 0) {
        throw new Exception("Invalid user ID");
    }

    $db = Database::initDefault();
    $conn = $db->getConnection();
    $dbUtil = new DatabaseUtil($conn);

    $details = $dbUtil->getUserDetails($userId);

    if ($details) {
        echo json_encode($details);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
