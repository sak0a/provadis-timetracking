<?php
header('Content-Type: application/json');

require_once '../backend/database/Database.php';

use backend\database\Database;

try {
    $projectId = isset($_GET['projectId']) ? intval($_GET['projectId']) : 0;

    if ($projectId <= 0) {
        throw new Exception("Invalid project ID");
    }

    $db = Database::initDefault();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT task_id, task_name FROM Tasks WHERE project_id = ?");
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $tasks = $result->fetch_all(MYSQLI_ASSOC);

    if ($tasks) {
        echo json_encode($tasks);
    } else {
        echo json_encode([]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
