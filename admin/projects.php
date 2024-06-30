<?php 
require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';
if (strpos($_SERVER['REQUEST_URI'], 'admin/projects.php') !== false) {
    echo $_SERVER['REQUEST_URI'];
    header("Location: ../admin");
    exit();
}

use backend\database\Database;
use backend\database\DatabaseUtil;

// Initialisierung der Datenbankverbindung
$db = Database::initDefault();
$dbUtil = new DatabaseUtil($db->getConnection());
// Abrufen aller Projekte
$projects = $dbUtil->getAllProjects();
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
        <h2>Projektverwaltung</h2>
        <button class="button" onclick="document.getElementById('addProjectModal').style.display='block'">Projekt hinzufügen</button>
    </div>
    <div id="projectDetails"></div>
    <!-- Projektübersicht -->
    <div class="section">
        <h2>Übersicht über Projekte und Tätigkeiten</h2>
        <table>
    <tr>
        <th>Projekt-ID</th>
        <th>Projektname</th>
        <th>Projektleiter</th>
        <th>Startdatum</th>
        <th>Enddatum</th>
        <th>Status</th>
    </tr>
    <?php foreach ($projects as $project): ?>
    <tr>
                <td><a href="#" onclick="showProjectDetails(event, <?php echo htmlspecialchars($project['Projekt-ID']); ?>)"><?php echo htmlspecialchars($project['Projekt-ID']); ?></a></td>
                <td><a href="#" onclick="showProjectDetails(event, <?php echo htmlspecialchars($project['Projekt-ID']); ?>)"><?php echo htmlspecialchars($project['Projektname']); ?></a></td>
                <td><?php  echo htmlspecialchars($project['Projektleiter_Vorname']) . ' ' . htmlspecialchars($project['Projektleiter_Nachname']) ;?></td>
                <td><?php echo htmlspecialchars($project['Startdatum']); ?></td>
                <td><?php echo htmlspecialchars($project['Enddatum']); ?></td>
                <td><?php echo htmlspecialchars($project['Status']); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

    </div>

    
</div>
<!-- Modal for adding a new user -->
<div id="addProjectModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addProjectModal').style.display='none'">Close &times;</span>
        <h2>Neues Projekt hinzufügen</h2>
        <form id="addProjectForm" method="post" action="../backend/add_project.php">
            <div class="form-group">
                <label for="project_name">Projektname</label> <!--es wird in die tabelle Projects geschrieben-->
                <input type="text" id="project_name" name="project_name" required>
            </div>
            <div class="form-group">
                <label for="start_date">Startdatum</label> <!--es wird in die tabelle Projects geschrieben-->
                <input type="date" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="status_id">Status</label> <!--es wird in die tabelle Projects geschrieben-->
                <select id="status_id" name="status_id" required>
                    <option value="9">Abgeschlossen</option>
                    <option value="10">In Bearbeitung</option>
                    <option value="12">Pausiert</option>
                    <option value="11">Abgebrochen</option>
                </select>
            </div>
             <div class="form-group">
                <label for="department_head">Verantwortlicher</label> <!--es wird in die tabelle UserRoles (user_id)(project_id)(role_id) geschrieben-->
                <select id="department_head" name="department_head" required>
                    <option value="1">Alex</option>
                    <option value="2">maria</option>
                    <option value="3">anton</option>
                    <option value="4">Laurin'o</option>
                </select>
            </div>
            <button type="submit" class="button">Projekt hinzufügen</button>
        </form>
    </div>
</div>

<!-- Modal for more details -->
<div id="moreDetails" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">Close &times;</span>
        <div id="projectDetailsContent"></div>
    </div>
</div>
</body>
</html>
