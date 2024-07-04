<?php
header('Content-Type: application/json');

require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

$userId = 2300;  // Beispielhafte Benutzer-ID

// Datenbankverbindung
$db = Database::initDefault();
$conn = $db->getConnection();

// SQL-Abfrage zur Abrufung der Benutzerstatistiken
$sql = "SELECT
                    COALESCE(SUM(TIMESTAMPDIFF(HOUR, te.start_time, te.end_time)), 0) AS total_hours_worked,
                    COALESCE(SUM(CASE WHEN te.start_time >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN TIMESTAMPDIFF(HOUR, te.start_time, te.end_time) ELSE 0 END), 0) AS hours_last_month,
                    COALESCE(SUM(CASE WHEN te.start_time >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) THEN TIMESTAMPDIFF(HOUR, te.start_time, te.end_time) ELSE 0 END), 0) AS hours_last_3_months,
                    COALESCE(SUM(CASE WHEN te.start_time >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) THEN TIMESTAMPDIFF(HOUR, te.start_time, te.end_time) ELSE 0 END), 0) AS hours_last_6_months
                 FROM TimeEntries te
                 WHERE te.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode($data);
