<?php 
// if (strpos($_SERVER['REQUEST_URI'], 'admin/employees.php') !== false) {
//     echo $_SERVER['REQUEST_URI'];
//     header("Location: ../admin");
//     exit();
// }
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
                <option value="admin">Admin</option>
                <option value="user">Mitarbeiter</option>
            </select>
            <button class="button">Filtern</button>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Email</th>
                <th>Rolle</th>
            </tr>
            <tr>
                <td>1</td>
                <td>Max</td>
                <td>Mustermann</td>
                <td>max@mustermann.de</td>
                <td>Admin</td>
            </tr>
            <!-- Weitere Zeilen -->
        </table>
    </div>

    
<!-- Modal for adding a new user -->
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
                    <option value="7">Projektverantwortlicher</option>
                    <option value="8">Mitarbeiter</option>
                </select>
            </div>
            <div class="form-group">
                <label for="entry_date">Eintrittsdatum:</label>
                <input type="date" id="entry_date" name="entry_date" required>
            </div>
            <button type="submit" class="button">Benutzer hinzufügen</button>
        </form>
    </div>
</div>
</body>
</html>
