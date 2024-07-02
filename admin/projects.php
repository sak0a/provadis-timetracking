<?php /** @noinspection LanguageDetectionInspection */
require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';

use backend\database\Database;
use backend\database\DatabaseUtil;

if (str_contains($_SERVER['REQUEST_URI'], 'admin/projects.php')) {
    echo $_SERVER['REQUEST_URI'];
    header("Location: ../admin");
    exit();
}

/**
 * Initialize Database
 */
$db = Database::initDefault();
$dbUtil = new DatabaseUtil($db->getConnection());

// Abrufen aller Projekte
$projects = $dbUtil->getAllProjects();
?>
<div class="container">
    
    <!-- Benutzerverwaltung -->
    <div class="section">
        <h2>Projektverwaltung</h2>
        <button class="button" onclick="document.getElementById('addProjectModal').style.display='block'">Projekt hinzufügen</button>
    </div>

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
        <td><?php echo htmlspecialchars($project['project_id']); ?></td>
        <td><?php echo htmlspecialchars($project['project_name']); ?></td>
        <td><?php  ?></td> <!-- Sie sollten hier den Namen des Projektleiters abrufen, falls erforderlich -->
        <td><?php echo htmlspecialchars($project['start_date']); ?></td>
        <td><?php echo htmlspecialchars($project['end_date']); ?></td>
        <td><?php echo htmlspecialchars($project['status_id']); ?></td>
    </tr>
    <?php endforeach; ?>
</table>
    </div>

    
</div>
<!-- Modal for adding a new user -->
<div id="addProjectModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addProjectModal').style.display='none'">&times;</span>
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
