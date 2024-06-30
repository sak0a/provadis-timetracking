<?php 
require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';
if (strpos($_SERVER['REQUEST_URI'], 'admin/employees.php') !== false) {
    echo $_SERVER['REQUEST_URI'];
    header("Location: ../admin");
    exit();
}

use backend\database\Database;
use backend\database\DatabaseUtil;

// Initialisierung der Datenbankverbindung
$db = Database::initDefault();
$dbUtil = new DatabaseUtil($db->getConnection());

// Abrufen aller Benutzer und Projekte
$users = $dbUtil->getAllUsers();
$projects = $dbUtil->getAllProjects();
$userProjects = [];
foreach ($projects as $project) {
    if (!empty($project['Projektleiter_ID'])) {
        $userProjects[$project['Projektleiter_ID']][] = $project['Projektname'];
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CommerzBau</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
    <script>    
    </script>
</head>
<body>

<div class="container">
    <!-- Benutzerverwaltung -->
    <div class="section">
        <h2>Benutzerverwaltung</h2>
        <button class="button" onclick="document.getElementById('addUserModal').style.display='block'">Benutzer hinzufügen</button>
    </div>

    <!-- Benutzerübersicht -->
    <div class="section">
        <h2>Übersicht über alle Benutzer</h2>
        <div class="filter">
            <input type="text" placeholder="Nach Name suchen">
            <select>
                <option value="">Alle Rollen</option>
                <option value="6">Admin</option>
                <option value="7">Projektleiter</option>
                <option value="8">Mitarbeiter</option>
            </select>
            <button class="button">Filtern</button>
        </div>

        <table>
            <tr>
                <th>Mitarbeiter-ID</th>
                <th>Nachname, Vorname</th>
                <th>Email</th>
                <th>Rolle</th>
                <th>Projekt</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><a href="#" onclick="showUserDetails(event, <?php echo htmlspecialchars($user['user_id']); ?>)"><?php echo htmlspecialchars($user['user_id']); ?></a></td>
                <td><a href="#" onclick="showUserDetails(event, <?php echo htmlspecialchars($user['user_id']); ?>)"><?php echo htmlspecialchars($user['last_name']) . ', ' . htmlspecialchars($user['first_name']); ?></a></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php 
                    $role = htmlspecialchars($user['role_id']);
                    if ($role == 6) {
                        echo 'Admin';
                    } elseif ($role == 7) {
                        echo 'Projektleiter';
                    } elseif ($role == 8) {
                        echo 'Mitarbeiter';
                    }
                ?></td>
                <td>
                    <?php 
                        $userId = htmlspecialchars($user['user_id']);
                        if (isset($userProjects[$userId])) {
                            echo htmlspecialchars(implode(', ', $userProjects[$userId]));
                        } else {
                            echo 'Kein Projekt';
                        }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- Modal for adding a new user -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addUserModal').style.display='none'">&times;</span>
        <h2>Neuen Benutzer hinzufügen</h2>
        <form id="addUserForm" method="post" action="../backend/add_user.php">
            <div class="form-group">
                <label for="personal_number">Personalnummer:</label>
                <input type="text" id="personal_number" name="personal_number" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="first_name">Vorname:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Nachname:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="birthdate">Geburtsdatum:</label>
                <input type="date" id="birthdate" name="birthdate" required>
            </div>
            <div class="form-group">
                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role_id">Rolle:</label>
                <select id="role_id" name="role_id" required>
                    <option value="6">Admin</option>
                    <option value="7">Projektleiter</option>
                    <option value="8">Mitarbeiter</option>
                </select>
            </div>
            <button type="submit" class="button">Benutzer hinzufügen</button>
        </form>
    </div>
</div>

<!-- Modal for more details -->
<div id="moreDetails" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">Close &times;</span>
        <div id="userDetailsContent"></div>
    </div>
</div>

</body>
</html>