<?php
session_start();
require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

try {
    $db = Database::initDefault();
    $conn = $db->getConnection();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['absences_id'])) {
        $absences_id = $_POST['absences_id'];

        $sql = "DELETE FROM Absences WHERE absence_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $absences_id);
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
