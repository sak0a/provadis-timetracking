<?php
header('Content-Type: application/json');
require_once '../backend/Auth.php';
require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;


$db = Database::initDefault();
$dbUtil = new DatabaseUtil($db->getConnection());

$user_id = $_SESSION['user_id']; // Holen Sie sich die Benutzer-ID aus der Session
$months = isset($_POST['months']) ? (int)$_POST['months'] : 0;
$direction = 'previous';
if (isset($_POST['next'])) {
    $direction = 'next';
}

$data = $util->getTableContent($user_id, $months, $direction);

echo json_encode($data);
?>