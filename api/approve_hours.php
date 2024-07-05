<?php
include '../backend/database/Database.php';
include '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

$db = Database::initDefault();
$dbUtil = new DatabaseUtil($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id']) && isset($_POST['project_id']) && isset($_POST['manager_id'])) {
    $userId = intval($_POST['user_id']);
    $projectId = intval($_POST['project_id']);
    $managerId = intval($_POST['manager_id']);

    // Zeitbereich für den letzten Monat berechnen
    $startDate = date('Y-m-d', strtotime('first day of last month'));
    $endDate = date('Y-m-d', strtotime('last day of last month'));

    $sqlApprove = "UPDATE TimeEntries
                   SET approved_by = ?
                   WHERE user_id = ? 
                   AND project_id = ?
                   AND approved_by IS NULL
                   AND start_time >= ? 
                   AND start_time <= ?";
    $stmtApprove = $db->getConnection()->prepare($sqlApprove);
    $stmtApprove->bind_param("iiiss", $managerId, $userId, $projectId, $startDate, $endDate);

    if ($stmtApprove->execute()) {
        echo json_encode(['success' => 'Hours approved successfully']);
    } else {
        echo json_encode(['error' => 'Failed to approve hours']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
