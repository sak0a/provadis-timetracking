<?php
header('Content-Type: application/json');
require_once '../backend/Auth.php';
require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

session_start();

try {
    // Check if the user is authenticated
    if (!Auth::isLoggedIn()) {
        throw new Exception("Unauthorized access");
    }

    $userPersonalNumber = isset($_GET['s_pn']) ? intval($_GET['s_pn']) : 0;

    // 7 digits personal number is required
    if ($userPersonalNumber < 1000000) {
        throw new Exception("Invalid Request");
    }

    $db = Database::initDefault();
    $dbUtil = new DatabaseUtil($db->getConnection());

    $userDetails = $dbUtil->getUserDetailsByPersonalNumber($userPersonalNumber);

    if ($userDetails) {
        echo json_encode($userDetails);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}