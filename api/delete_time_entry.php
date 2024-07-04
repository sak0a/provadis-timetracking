<?php
session_start();
require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

try {
    $db = Database::initDefault();
    $conn = $db->getConnection();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['time_entry_id'])) {
        $time_entry_id = $_POST['time_entry_id'];

        $sql = "DELETE FROM TimeEntries WHERE time_entry_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $time_entry_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Entry not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}