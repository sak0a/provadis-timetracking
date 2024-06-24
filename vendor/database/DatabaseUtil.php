<?php

use vendor\database\Database;


class DatabaseUtil {
    private mysqli $database;

    public function __construct($db) {
        $this->database = $db;
    }

    // CRUD-Methoden für Benutzer
    public function userExists($username, $email) {
        $sql = "SELECT COUNT(*) FROM Users WHERE email = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$username, $email]);
        return $stmt->fetchColumn() > 0;
    }

    // Erstellen eines Benutzers
    public function createUser($firstName, $lastName, $email, $password, $role) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO Users (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$firstName, $lastName, $email, $passwordHash, $role]);
    }

    // Abrufen eines Benutzers
    public function getUser($user_id) {
        $sql = "SELECT * FROM Users WHERE user_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch();
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

    // CRUD-Methoden für Projekte

    // Erstellen eines Projekts
    public function createProject($project_name, $project_manager_id, $start_date, $end_date, $status) {
        $sql = "INSERT INTO Projects (project_name, project_manager_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$project_name, $project_manager_id, $start_date, $end_date, $status]);
    }

    // Abrufen eines Projekts
    public function getProject($project_id) {
        $sql = "SELECT * FROM Projects WHERE project_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$project_id]);
        return $stmt->fetch();
    }

    // Bearbeiten eines Projekts
    public function updateProject($project_id, $project_name, $start_date, $end_date, $status) {
        $sql = "UPDATE Projects SET project_name = ?, start_date = ?, end_date = ?, status = ? WHERE project_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$project_name, $start_date, $end_date, $status, $project_id]);
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