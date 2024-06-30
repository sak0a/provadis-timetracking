<?php
namespace backend\database;
use mysqli;


class DatabaseUtil {
    private mysqli $database;

    public function __construct($db) {
        $this->database = $db;
    }

    // CRUD-Methoden für Benutzer
    public function userExists($email) {
        $sql = "SELECT email FROM Users WHERE email = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM Users WHERE email = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Erstellen eines Benutzers
    public function createUser($personal_number, $email, $firstName, $lastName, $birthdate, $password, $role, $entry_date) {
    public function createUser($personal_number, $email, $firstName, $lastName, $birthdate, $password, $role, $entry_date) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO Users (personal_number, email, first_name, last_name, birthdate, password_hash, role_id, entry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = "INSERT INTO Users (personal_number, email, first_name, last_name, birthdate, password_hash, role_id, entry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$personal_number, $email, $firstName, $lastName, $birthdate, $passwordHash, $role, $entry_date]);
        return $stmt->execute([$personal_number, $email, $firstName, $lastName, $birthdate, $passwordHash, $role, $entry_date]);
    }

    // Abrufen eines Benutzers
    public function getUser($user_id) {
        $sql = "SELECT * FROM Users WHERE user_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    public function getAllUsers() {
        $sql = "SELECT * FROM Users";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }  

    // Bearbeiten eines Benutzers
    public function updateUser($user_id, $firstName, $lastName, $email, $role) {
        $sql = "UPDATE Users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE user_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$firstName, $lastName, $email, $role, $user_id]);
    }

    // Löschen eines Benutzers
    public function deleteUser($user_id) {
        $sql = "DELETE FROM Users WHERE user_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$user_id]);
    }

    // Informationen für den Nutzer aufrufen
    public function getUserDetails($userId) {
        $userDetails = [];

        // Benutzerstammdaten abrufen
        $sqlUser = "SELECT user_id, personal_number, email, first_name, last_name, birthdate, role_id 
                    FROM Users 
                    WHERE user_id = ?";
        $stmtUser = $this->database->prepare($sqlUser);
        $stmtUser->bind_param("i", $userId);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();
        if ($resultUser->num_rows > 0) {
            $userDetails['user'] = $resultUser->fetch_assoc();
        } else {
            return null; // Benutzer nicht gefunden
        }

        // Gearbeitete Stunden abrufen
        $sqlHours = "SELECT
                        COALESCE(SUM(TIMESTAMPDIFF(HOUR, te.start_time, te.end_time)), 0) AS total_hours_worked,
                        COALESCE(SUM(CASE WHEN te.start_time >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN TIMESTAMPDIFF(HOUR, te.start_time, te.end_time) ELSE 0 END), 0) AS hours_last_month,
                        COALESCE(SUM(CASE WHEN te.start_time >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) THEN TIMESTAMPDIFF(HOUR, te.start_time, te.end_time) ELSE 0 END), 0) AS hours_last_3_months,
                        COALESCE(SUM(CASE WHEN te.start_time >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) THEN TIMESTAMPDIFF(HOUR, te.start_time, te.end_time) ELSE 0 END), 0) AS hours_last_6_months
                     FROM TimeEntries te
                     WHERE te.user_id = ?";
        $stmtHours = $this->database->prepare($sqlHours);
        $stmtHours->bind_param("i", $userId);
        $stmtHours->execute();
        $resultHours = $stmtHours->get_result();
        $userDetails['hours'] = $resultHours->fetch_assoc();

        // Projekte und Aufgaben des Benutzers abrufen
        $sqlProjects = "SELECT 
                            p.project_id,
                            p.project_name,
                            p.start_date,
                            p.end_date,
                            ps.status_name AS project_status,
                            t.task_id,
                            t.task_name,
                            t.created_at,
                            t.updated_at,
                            (SELECT COALESCE(SUM(TIMESTAMPDIFF(HOUR, te.start_time, te.end_time)), 0)
                             FROM TimeEntries te
                             WHERE te.project_id = p.project_id AND te.user_id = ?) AS total_hours_on_project
                        FROM 
                            Projects p
                        LEFT JOIN 
                            UserRoles ur ON p.project_id = ur.project_id
                        LEFT JOIN 
                            ProjectStatus ps ON p.status_id = ps.status_id
                        LEFT JOIN 
                            Tasks t ON p.project_id = t.project_id
                        WHERE 
                            ur.user_id = ?";
        $stmtProjects = $this->database->prepare($sqlProjects);
        $stmtProjects->bind_param("ii", $userId, $userId);
        $stmtProjects->execute();
        $resultProjects = $stmtProjects->get_result();
        $userDetails['projects'] = $resultProjects->fetch_all(MYSQLI_ASSOC);

        return $userDetails;
    }

    // CRUD-Methoden für Projekte

    public function projectExists($project_name) {
        $sql = "SELECT project_name FROM Projects WHERE project_name = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $project_name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function projectExists($project_name) {
        $sql = "SELECT project_name FROM Projects WHERE project_name = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $project_name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    // Erstellen eines Projekts
    public function createProject($project_name, $start_date, $status_id) {
        $sql = "INSERT INTO Projects (project_name, start_date, status_id) VALUES (?, ?, ?)";
    public function createProject($project_name, $start_date, $status_id) {
        $sql = "INSERT INTO Projects (project_name, start_date, status_id) VALUES (?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$project_name, $start_date, $status_id]);
        return $stmt->execute([$project_name, $start_date, $status_id]);
    }

    // Abrufen eines Projekts
    public function getProject($project_id) {
        $sql = "SELECT * FROM Projects WHERE project_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$project_id]);  
        return $stmt->fetch();
    }
    public function getAllProjects() {
        $sql = "SELECT 
                p.project_id AS 'Projekt-ID',
                p.project_name AS 'Projektname',
                u.user_id AS 'Projektleiter_ID',
                u.first_name AS 'Projektleiter_Vorname',
                u.last_name AS 'Projektleiter_Nachname',
                p.start_date AS 'Startdatum',
                p.end_date AS 'Enddatum',
                ps.status_name AS 'Status'
            FROM 
                Projects p
            LEFT JOIN 
                UserRoles ur ON p.project_id = ur.project_id AND ur.role_id = 7
            LEFT JOIN 
                Users u ON ur.user_id = u.user_id
            LEFT JOIN 
                ProjectStatus ps ON p.status_id = ps.status_id";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
        return $projects;
    }  

    public function getProjectDetails($projectId) {
        $sql = "CALL GetProjectDetails(?)";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();

        $details = $result->fetch_assoc();

        return $details;
    }

    // Bearbeiten eines Projekts
    public function updateProject($project_id, $project_name, $start_date, $end_date, $status_id) {
        $sql = "UPDATE Projects SET project_name = ?, start_date = ?, end_date = ?, status_id = ? WHERE project_id = ?";
    public function updateProject($project_id, $project_name, $start_date, $end_date, $status_id) {
        $sql = "UPDATE Projects SET project_name = ?, start_date = ?, end_date = ?, status_id = ? WHERE project_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$project_name, $start_date, $end_date, $status_id, $project_id]);
        return $stmt->execute([$project_name, $start_date, $end_date, $status_id, $project_id]);
    }

    // Löschen eines Projekts
    public function deleteProject($project_id) {
        $sql = "DELETE FROM Projects WHERE project_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$project_id]);
    }

    // CRUD-Methoden für Zeiterfassungen (Time Entries)

    // Erstellen eines Zeiteintrags
    public function createTimeEntry($user_id, $project_id, $task_id, $start_time, $end_time, $description, $approved_by) {
        $duration = $end_time->diff($start_time)->format('%H:%I:%S');
        $sql = "INSERT INTO TimeEntries (user_id, project_id, task_id, start_time, end_time, duration, description, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$user_id, $project_id, $task_id, $start_time->format('Y-m-d H:i:s'), $end_time->format('Y-m-d H:i:s'), $duration, $description, $approved_by]);
    }

    // Abrufen eines Zeiteintrags
    public function getTimeEntry($time_entry_id) {
        $sql = "SELECT * FROM TimeEntries WHERE time_entry_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$time_entry_id]);
        return $stmt->fetch();
    }

    // Bearbeiten eines Zeiteintrags
    public function updateTimeEntry($time_entry_id, $start_time, $end_time, $description) {
        $duration = $end_time->diff($start_time)->format('%H:%I:%S');
        $sql = "UPDATE TimeEntries SET start_time = ?, end_time = ?, duration = ?, description = ? WHERE time_entry_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$start_time->format('Y-m-d H:i:s'), $end_time->format('Y-m-d H:i:s'), $duration, $description, $time_entry_id]);
    }

    // Löschen eines Zeiteintrags
    public function deleteTimeEntry($time_entry_id) {
        $sql = "DELETE FROM TimeEntries WHERE time_entry_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$time_entry_id]);
    }
}
?>