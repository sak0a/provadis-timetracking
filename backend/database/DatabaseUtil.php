<?php
namespace backend\database;
use backend\database\Database;
use backend\database\Filter;
use mysqli;


class DatabaseUtil
{
    private mysqli $database;

    public function __construct($db)
    {
        $this->database = $db;
    }

    public function checkForAbsenceOverlap($user_id, $datePickerAbsencesStart, $datePickerAbsencesEnd): bool
    {
        // Überprüfen, ob es bestehende Überschneidungen gibt
        $checkSql = "SELECT COUNT(*) FROM Absences WHERE user_id = ? AND 
                 NOT (end_date < ? OR start_date > ?)";
        $checkStmt = $this->database->prepare($checkSql);
        $checkStmt->bind_param('iss',
            $user_id,
            $datePickerAbsencesStart,
            $datePickerAbsencesEnd);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        // Wenn keine Überschneidungen gefunden wurden, true zurückgeben
        return $count == 0;
    }


    public function checkForOverlap($user_id, $start_date, $end_date): bool
    {
        // Überprüfen, ob es Überschneidungen mit Absences gibt
        $checkAbsencesSql = "SELECT COUNT(*) FROM Absences WHERE user_id = ? AND 
                         NOT (end_date < ? OR start_date > ?)";
        $checkAbsencesStmt = $this->database->prepare($checkAbsencesSql);
        $checkAbsencesStmt->bind_param('iss',
            $user_id,
            $start_date,
            $end_date);
        $checkAbsencesStmt->execute();
        $checkAbsencesStmt->bind_result($countAbsences);
        $checkAbsencesStmt->fetch();
        $checkAbsencesStmt->close();

        // Überprüfen, ob es Überschneidungen mit timeentries gibt (nur das Datum von start_time berücksichtigen)
        $checkTimeEntriesSql = "SELECT COUNT(*) FROM TimeEntries WHERE user_id = ? AND 
                            DATE(start_time) = DATE(?)";
        $checkTimeEntriesStmt = $this->database->prepare($checkTimeEntriesSql);
        $checkTimeEntriesStmt->bind_param('is',
            $user_id,
            $start_date);
        $checkTimeEntriesStmt->execute();
        $checkTimeEntriesStmt->bind_result($countTimeEntries);
        $checkTimeEntriesStmt->fetch();
        $checkTimeEntriesStmt->close();

        // Wenn keine Überschneidungen gefunden wurden, true zurückgeben
        return $countAbsences == 0 && $countTimeEntries == 0;
    }

    public function createAbsencesEntry($user_id, $artAbsences, $datePickerAbsencesStart, $datePickerAbsencesEnd): bool
    {
        // Überprüfen, ob das Enddatum nach oder gleich dem Startdatum liegt
        if (strtotime($datePickerAbsencesEnd) < strtotime($datePickerAbsencesStart)) {
            // Wenn das Enddatum vor dem Startdatum liegt, false zurückgeben
            echo "<script>alert('Das Enddatum muss nach oder gleich dem Startdatum liegen.');window.location.href='http://localhost:3001/';</script>";
            return false;
        }

        // Überprüfen, ob es Überschneidungen mit Absences oder TimeEntries gibt
        if (!$this->checkForOverlap($user_id, $datePickerAbsencesStart, $datePickerAbsencesEnd)) {
            // Wenn Überschneidungen gefunden wurden, false zurückgeben
    echo "<script>alert('Es gibt eine Überschneidung mit einem vorhandenen Eintrag.');window.location.href='http://localhost:3001/';</script>";
            return false;
        }

        // Überprüfen, ob das Startdatum und das Enddatum im aktuellen Monat liegen
        $aktuellerMonatStart = date('Y-m-01');
        $aktuellerMonatEnde = date('Y-m-t');
        if (strtotime($datePickerAbsencesStart) < strtotime($aktuellerMonatStart) || strtotime($datePickerAbsencesEnd) > strtotime($aktuellerMonatEnde)) {
            // Wenn das Startdatum oder Enddatum nicht im aktuellen Monat liegt, false zurückgeben
            echo "<script>alert('Das Startdatum und das Enddatum müssen im aktuellen Monat liegen.');window.location.href='http://localhost:3001/';</script>";
            return false;
        }

        // Eintrag einfügen
        $sql = "INSERT INTO Absences (user_id, type_id, start_date, end_date) VALUES (?, ?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('iiss', $user_id, $artAbsences, $datePickerAbsencesStart, $datePickerAbsencesEnd);
        $result = $stmt->execute();
        $stmt->close();

        // Rückgabe des Einfügeergebnisses
        return $result;
    }



    public function getProjectCountByFilter(Filter $filter): int
    {
        $sql = "SELECT COUNT(project_id) AS total FROM Projects";
        if ($filter->hasFilters())
            $sql .= $filter->toWhereSQL();
        $result = $this->database->query($sql);
        if ($row = $result->fetch_assoc()) {
            return $row['total'];
        }
        return 0;
    }

    public function getLeadersByProjectId($projectId): array
    {
        $stmt = $this->database->prepare("
            SELECT u.user_id as id, u.first_name, u.last_name
            FROM UserRoles ur
            JOIN Users u ON ur.user_id = u.user_id
            WHERE ur.project_id = ? AND ur.role_id = (SELECT role_id FROM Roles WHERE role_name = 'Projektverantwortlicher')
        ");
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        $leaders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $leaders;
    }

    public function getEmployeesByProjectId($projectId): array
    {
        $stmt = $this->database->prepare("
            SELECT u.user_id as id, u.first_name, u.last_name
            FROM UserRoles ur
            JOIN Users u ON ur.user_id = u.user_id
            WHERE ur.project_id = ? AND ur.role_id = (SELECT role_id FROM Roles WHERE role_name = 'Mitarbeiter')
        ");
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $employees;
    }

    public function getTasksByProjectId($projectId): array
    {
        $stmt = $this->database->prepare("
            SELECT t.task_id as id, t.task_name as name
            FROM Tasks t
            WHERE project_id = ?
        ");
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        $tasks = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $tasks;
    }

    public function getUserIdsByRole($leaders) {
        $leaderArray = explode(',', $leaders);
        $placeholders = implode(',', array_fill(0, count($leaderArray), '?'));
        $leaderParams = array_map(function($leader) { return "%$leader%"; }, $leaderArray);

        $query = "
            SELECT DISTINCT ur.project_id
            FROM UserRoles ur
            JOIN Users u ON ur.user_id = u.user_id
            WHERE ur.role_id = (SELECT role_id FROM Roles WHERE role_name = 'leader') 
            AND (" . implode(' OR ', array_fill(0, count($leaderArray), "(u.first_name LIKE ? OR u.last_name LIKE ?)")) . ")
        ";

        $stmt = $this->database->prepare($query);

        // Bind the parameters dynamically
        $types = str_repeat('s', count($leaderParams) * 2);
        $params = [];
        foreach ($leaderParams as $leader) {
            $params[] = $leader;
            $params[] = $leader;
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $projectIds = $result->fetch_all(MYSQLI_COLUMN, 0);
        $stmt->close();
        return $projectIds;
    }


    // START ROLES ------------------------------------------------------------------------------------------------
    public function getRole($name): false|array|null
    {
        $sql = "SELECT * FROM Roles WHERE role_name = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function getRolesLike($name): array
    {
        $sql = "SELECT * FROM Roles WHERE role_name LIKE ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        return $roles;
    }
    public function getRoleById($id): false|array|null
    {
        $sql = "SELECT * FROM Roles WHERE role_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function getAllRoles(): array
    {
        $sql = "SELECT * FROM Roles";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        return $roles;
    }
    public function roleExists($name): bool
    {
        $sql = "SELECT role_name FROM Roles WHERE role_name = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() !== null;
    }
    public function createRole($name): bool
    {
        $sql = "INSERT INTO Roles (role_name) VALUES (?)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$name]);
    }
    public function deleteRole($name): bool
    {
        $sql = "DELETE FROM Roles WHERE role_name = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$name]);
    }
    public function updateRole($name): bool
    {
        $sql = "UPDATE Roles SET role_name = ? WHERE role_name = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$name]);
    }
    // END ROLES ------------------------------------------------------------------------------------------------

    // START ABSENCE TYPES ------------------------------------------------------------------------------------------------
    public function getAbsenceType($name): false|array|null
    {
        $sql = "SELECT * FROM AbsenceTypes WHERE type_name = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function getAllAbsenceTypes(): array
    {
        $sql = "SELECT * FROM AbsenceTypes";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        return $roles;
    }
    public function absenceTypeExists($name): bool
    {
        $sql = "SELECT type_name FROM AbsenceTypes WHERE type_name = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() !== null;
    }
    public function createAbsenceType($name): bool
    {
        $sql = "INSERT INTO AbsenceTypes (type_name) VALUES (?)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$name]);
    }
    public function deleteAbsenceType($name): bool
    {
        $sql = "DELETE FROM AbsenceTypes WHERE type_name = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$name]);
    }
    public function updateAbsenceType($name): bool
    {
        $sql = "UPDATE AbsenceTypes SET type_name = ? WHERE type_name = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$name]);
    }
    // END ABSENCE TYPES ------------------------------------------------------------------------------------------------

    // START PROJECT STATUS ------------------------------------------------------------------------------------------------
    public function getProjectStatus($name): array
    {
        $sql = "SELECT * FROM Roles WHERE status_name = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function getProjectStatusById($id): array
    {
        $sql = "SELECT * FROM ProjectStatus WHERE status_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function getProjectStatusLike($name): array
    {
        $sql = "SELECT * FROM ProjectStatus WHERE status_name LIKE ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        return $roles;
    }
    public function getAllProjectStatuses(): array
    {
        $sql = "SELECT * FROM ProjectStatus";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        return $roles;
    }
    public function projectStatusExists($name): bool
    {
        $sql = "SELECT status_name FROM ProjectStatus WHERE status_name = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() !== null;
    }
    public function createProjectStatus($name): bool
    {
        $sql = "INSERT INTO ProjectStatus (status_name) VALUES (?)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$name]);
    }
    public function deleteProjectStatus($name): bool
    {
        $sql = "DELETE FROM ProjectStatus WHERE status_name = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$name]);
    }
    public function updateProjectStatus($name): bool
    {
        $sql = "UPDATE ProjectStatus SET status_name = ? WHERE status_name = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$name]);
    }
    // END PROJECT STATUS ------------------------------------------------------------------------------------------------


    // START USER ------------------------------------------------------------------------------------------------
    public function userExists($email): bool
    {
        $sql = "SELECT email FROM Users WHERE email = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() !== null;
    }

    public function getUserById($userId): array
    {
        $sql = "SELECT * FROM Users WHERE user_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
    public function getUserByEmail($email): false|array|null
    {
        $sql = "SELECT * FROM Users WHERE email = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function createUser($personal_number, $email, $firstName, $lastName, $birthdate, $password, $role, $entry_date): bool
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO Users (personal_number, email, first_name, last_name, birthdate, password_hash, role_id, entry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$personal_number, $email, $firstName, $lastName, $birthdate, $passwordHash, $role, $entry_date]);
    }
    public function getUser($user_id): ?bool
    {
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
    public function updateUser($user_id, $firstName, $lastName, $email, $role)
    {
        $sql = "UPDATE Users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE user_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$firstName, $lastName, $email, $role, $user_id]);
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function deleteUser($user_id): bool
    {
        $sql = "DELETE FROM Users WHERE user_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$user_id]);
    }

    /**
     * @return int
     */
    public function getTotalUserCount(): int
    {
        $sql = "SELECT COUNT(user_id) AS total FROM Users";
        $result = $this->database->query($sql);
        if ($row = $result->fetch_assoc()) {
            return $row['total'];
        }
        return 0;
    }

    /**
     * @param Filter $filter
     * @return int
     */
    public function getUserCountByFilter(Filter $filter): int
    {
        $sql = "SELECT COUNT(*) AS total FROM Users";
        if ($filter->hasFilters())
            $sql .= $filter->toWhereSQL();
        $result = $this->database->query($sql);
        if ($row = $result->fetch_assoc()) {
            return $row['total'];
        }
        return 0;
    }
    public function getUserDetailsByPersonalNumber($personalNumber): ?array {
        $sql = "
            SELECT 
                user_id,
                personal_number,
                email,
                first_name,
                last_name,
                birthdate,
                role_id
            FROM 
                Users
            WHERE 
                personal_number = ?
        ";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $personalNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        $details = $result->fetch_assoc();
        return $details ? $details : null;
    }

    //zeiten

    public function getSaldo($user_id): string
    {
        $sql = "SELECT SEC_TO_TIME(SUM(
                TIME_TO_SEC(TIMEDIFF(te.end_time, te.start_time)) - 7.5 * 3600
            )) AS GesamtSaldo
            FROM
                TimeEntries te
            WHERE
                te.user_id = ?
                AND te.start_time >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH);";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $saldo = $row['GesamtSaldo'];

        // Falls kein Saldo vorhanden ist oder NULL ist, gib "00:00" zurück
        if ($saldo === null || $saldo === '00:00:00') {
            return "00:00";
        }

        // Überprüfe, ob der Saldo negativ ist und passe das Format entsprechend an
        if (substr($saldo, 0, 1) == '-') {
            $saldo = '-' . substr($saldo, 1, 5); // Negative Zeichen und hh:mm extrahieren
        } else {
            $saldo = substr($saldo, 0, 5); // Nur hh:mm extrahieren
        }

        // Umrechnung in Industrieminuten
        $saldo_industrial_minutes = $this->convertToIndustrialMinutes($saldo);

        return round($saldo_industrial_minutes / 60,2);
    }

    public function convertToIndustrialMinutes($saldo_hh_mm): int
    {
        // Überprüfen, ob das Saldo negativ ist und entsprechend das Vorzeichen speichern
        $is_negative = substr($saldo_hh_mm, 0, 1) == '-';

        // Entfernen des Vorzeichens, falls vorhanden, um die Stunden und Minuten korrekt zu extrahieren
        if ($is_negative) {
            $saldo_hh_mm = ltrim($saldo_hh_mm, '-');
        }

        // Aufteilen von Stunden und Minuten aus dem hh:mm Format
        list($hours, $minutes) = explode(':', $saldo_hh_mm);

        // Konvertierung der Zeit in Minuten
        $total_minutes = ($hours * 60) + $minutes;

        // Anwenden des Vorzeichens, falls das Saldo negativ ist
        if ($is_negative) {
            $total_minutes *= -1;
        }

        return $total_minutes;
    }

    // Informationen für den Nutzer aufrufen
    public function getUserDetails($personalNumber, $projectId = null): ?array
    {
        $userDetails = [];

        // Benutzerstammdaten abrufen
        $sqlUser = "SELECT *
                    FROM Users 
                    WHERE personal_number = ?";
        $stmtUser = $this->database->prepare($sqlUser);
        $stmtUser->bind_param("i", $personalNumber);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();
        if ($resultUser->num_rows > 0) {
            $userDetails['user'] = $resultUser->fetch_assoc();
        } else {
            return null; // Benutzer nicht gefunden
        }
        $userId = $userDetails['user']['user_id'];

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

        $sqlGeneralHours = "SELECT
                            COALESCE(SUM(CASE WHEN te.start_time >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND te.project_id = 184  THEN TIMESTAMPDIFF(HOUR, te.start_time, te.end_time) ELSE 0 END), 0) AS hours_last_month_allg_Arbeit,
                            COALESCE(SUM(CASE WHEN te.start_time >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) AND te.project_id = 184  THEN TIMESTAMPDIFF(HOUR, te.start_time, te.end_time) ELSE 0 END), 0) AS hours_last_3_months_allg_Arbeit,
                            COALESCE(SUM(CASE WHEN te.start_time >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND te.project_id = 184  THEN TIMESTAMPDIFF(HOUR, te.start_time, te.end_time) ELSE 0 END), 0) AS hours_last_6_months_allg_Arbeit,
                            COALESCE(SUM(CASE WHEN te.project_id = 184 THEN TIMESTAMPDIFF(HOUR, te.start_time, te.end_time) ELSE 0 END), 0) AS total_hours_allg_Arbeit
                            FROM TimeEntries te
                            WHERE te.user_id = ?";
        $stmtGeneralHours = $this->database->prepare($sqlGeneralHours);
        $stmtGeneralHours->bind_param("i", $userId);
        $stmtGeneralHours->execute();
        $resultGeneralHours = $stmtGeneralHours->get_result();
        $userDetails['general_hours'] = $resultGeneralHours->fetch_assoc();

        $sqlAbsences = "SELECT
                        COALESCE(SUM(CASE WHEN a.start_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN DATEDIFF(a.end_date, a.start_date) + 1 ELSE 0 END), 0) AS days_last_month_absences,
                        COALESCE(SUM(CASE WHEN a.start_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) THEN DATEDIFF(a.end_date, a.start_date) + 1 ELSE 0 END), 0) AS days_last_3_months_absences,
                        COALESCE(SUM(CASE WHEN a.start_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) THEN DATEDIFF(a.end_date, a.start_date) + 1 ELSE 0 END), 0) AS days_last_6_months_absences,
                        COALESCE(SUM(DATEDIFF(a.end_date, a.start_date) + 1), 0) AS total_days_absences
                        FROM Absences a
                        WHERE a.user_id = ?";
        $stmtAbsences = $this->database->prepare($sqlAbsences);
        $stmtAbsences->bind_param("i", $userId);
        $stmtAbsences->execute();
        $resultAbsences = $stmtAbsences->get_result();
        $userDetails["absences"] = $resultAbsences->fetch_assoc();

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
                            ur.user_id = ? LIMIT 5";
        $stmtProjects = $this->database->prepare($sqlProjects);
        $stmtProjects->bind_param("ii", $userId, $userId);
        $stmtProjects->execute();
        $resultProjects = $stmtProjects->get_result();
        $userDetails['projects'] = $resultProjects->fetch_all(MYSQLI_ASSOC);

        if ($projectId !== null) {
            // Spezifische Projektstunden und Status "approved_by" abrufen
            $sqlApprovedNull = "SELECT 
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
                                     WHERE te.project_id = p.project_id 
                                     AND te.user_id = ? 
                                     AND te.start_time >= DATE_SUB(LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)), INTERVAL DAY(LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))) - 1 DAY)
                                     AND te.start_time < DATE_ADD(DATE_SUB(CURDATE(), INTERVAL DAY(CURDATE()) DAY), INTERVAL 1 DAY)
                                     AND te.approved_by IS NULL) AS total_hours_on_project_last_month
                                FROM 
                                    Projects p
                                LEFT JOIN 
                                    UserRoles ur ON p.project_id = ur.project_id
                                LEFT JOIN 
                                    ProjectStatus ps ON p.status_id = ps.status_id
                                LEFT JOIN 
                                    Tasks t ON p.project_id = t.project_id
                                WHERE 
                                    ur.user_id = ? AND
                                    p.project_id = ?";
            $stmtApprovedNull = $this->database->prepare($sqlApprovedNull);
            $stmtApprovedNull->bind_param("iii", $userId, $userId, $projectId);
            $stmtApprovedNull->execute();
            $resultApprovedNull = $stmtApprovedNull->get_result();
            $userDetails["approved_null"] = $resultApprovedNull->fetch_assoc();
        }

        return $userDetails;
    }

    /**
     * @param Filter $filter Filter to apply
     * @param Number $page Page number
     * @param Number $pageSize Number of messages per page
     * @param String $order Order of the messages
     * @return array Message that match a filter
     */
    function getUsersByFilter(Filter $filter, $page, $pageSize, $order): array
    {
        $messages = array();

        $sql = "SELECT * FROM Users";

        if ($filter->hasFilters())
            $sql .= $filter->toWhereSQL();

        $offset = ($page - 1) * $pageSize;
        $sql .= " ORDER BY user_id $order";
        $sql .= " LIMIT $offset, $pageSize";

        $result = $this->database->query($sql);


        // page 90 (results 891-900) does not exist, so we get page the actual size of the query with the same filter and then remove 10 from
        if ($result->num_rows <= 0) {
            $realMessageCount = $this->getUserCountByFilter($filter);
            if ($realMessageCount > 0)
                // pagesize keeps the same but the page
                // only 783 results exists, so we divide 783 by 10 and get 78.3, then we subtract 1 and get 77.3, then we round down to 77 and get 770 results, which is the last page
                return $this->getUsersByFilter($filter, round($realMessageCount / 10, 0, PHP_ROUND_HALF_DOWN) - 1, $pageSize, $order);
            else
                return $messages;
        }

        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return $messages;
    }




    // CRUD-Methoden für Projekte
    public function projectExists($project_name): false|array|null
    {
        $sql = "SELECT project_name FROM Projects WHERE project_name = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $project_name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Erstellen eines Projekts
    public function createProject($project_name, $start_date, $status_id): bool
    {
        $sql = "INSERT INTO Projects (project_name, start_date, status_id) VALUES (?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$project_name, $start_date, $status_id]);
    }
    public function getProject($project_id): ?bool
    {
        $sql = "SELECT * FROM Projects WHERE project_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$project_id]);
        return $stmt->fetch();
    }
    public function getAllProjects(): array
    {
        $sql = "SELECT 
    p.project_id AS 'Projekt-ID',
    p.project_name AS 'Projektname',
    u.user_id AS 'Projektleiter_ID',
    u.first_name AS 'Projektleiter_Vorname',
    u.last_name AS 'Projektleiter_Nachname',
    p.start_date AS 'Startdatum',
    p.end_date AS 'Enddatum',
    ps.status_name AS 'Status',
    p.planned_time AS 'Geplannte Zeit',
    COALESCE(te.total_hours, 0) AS 'Gesamte Stunden'
FROM 
    Projects p
LEFT JOIN
    (SELECT ur.project_id, ur.user_id
     FROM UserRoles ur
     WHERE ur.role_id = 7) ur ON p.project_id = ur.project_id
LEFT JOIN
    Users u ON ur.user_id = u.user_id
LEFT JOIN
    ProjectStatus ps ON p.status_id = ps.status_id
LEFT JOIN 
    (SELECT 
        project_id, 
        SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)) AS total_hours 
     FROM 
        TimeEntries 
     GROUP BY 
        project_id
    ) te ON p.project_id = te.project_id
";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
        return $projects;
    }

    public function getAllProjectsForStatistic(): array
    {
        $sql = "SELECT 
    p.project_id AS 'Projekt-ID',
    p.project_name AS 'Projektname',
    GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name) SEPARATOR ', ') AS 'Projektleiter',
    p.start_date AS 'Startdatum',
    p.end_date AS 'Enddatum',
    ps.status_name AS 'Status',
    p.planned_time AS 'Geplannte Zeit',
    COALESCE(te.total_hours, 0) AS 'Gesamte Stunden'
FROM 
    Projects p
LEFT JOIN
    (SELECT ur.project_id, ur.user_id
     FROM UserRoles ur
     WHERE ur.role_id = 7) ur ON p.project_id = ur.project_id
LEFT JOIN
    Users u ON ur.user_id = u.user_id
LEFT JOIN
    ProjectStatus ps ON p.status_id = ps.status_id
LEFT JOIN 
    (SELECT 
        project_id, 
        SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)) AS total_hours 
     FROM 
        TimeEntries 
     GROUP BY 
        project_id
    ) te ON p.project_id = te.project_id
GROUP BY 
    p.project_id, 
    p.project_name, 
    p.start_date, 
    p.end_date, 
    ps.status_name, 
    p.planned_time
";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $project = [];
        while ($row = $result->fetch_assoc()) {
            $project[] = $row;
        }
        return $project;
    }
    public function getProjectTotalHours($projectId) {
        $stmt = $this->database->prepare("
            SELECT COALESCE(SUM(TIMESTAMPDIFF(HOUR, te.start_time, te.end_time)), 0) AS total_hours
            FROM TimeEntries te
            WHERE te.project_id = ?
        ");
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row['total_hours'];
        }
        return 0;
    }
    public function getProjectDetails($projectId) {
        $sqlProjectDetails = "
            SELECT 
                p.project_id AS projekt_id,
                p.project_name AS projektname,
                u.user_id AS projektleiter_id,
                u.first_name AS projektleiter_vorname,
                u.last_name AS projektleiter_nachname,
                p.start_date AS startdatum,
                p.end_date AS enddatum,
                ps.status_name AS status,
                p.planned_time AS geplannte_zeit,
                COALESCE(SUM(TIMESTAMPDIFF(HOUR, te.start_time, te.end_time)), 0) AS gesamte_stunden
            FROM 
                Projects p
            LEFT JOIN 
                UserRoles ur ON p.project_id = ur.project_id AND ur.role_id = 7
            LEFT JOIN 
                Users u ON ur.user_id = u.user_id
            LEFT JOIN 
                ProjectStatus ps ON p.status_id = ps.status_id
            LEFT JOIN 
                TimeEntries te ON p.project_id = te.project_id
            WHERE
                p.project_id = ?
            GROUP BY
                p.project_id, 
                p.project_name, 
                u.user_id, 
                u.first_name, 
                u.last_name, 
                p.start_date, 
                p.end_date, 
                ps.status_name, 
                p.planned_time
        ";

        $stmt = $this->database->prepare($sqlProjectDetails);
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        //$details = $result->fetch_assoc();
        $details["getProjectDetails"] = $result->fetch_assoc();

        $sqlProjectWorkers = "
            SELECT 
            u.personal_number,
                u.user_id,
                u.first_name,
                u.last_name
            FROM 
                Users u
            INNER JOIN 
                UserRoles ur ON u.user_id = ur.user_id
            WHERE 
                ur.project_id = ?
        ";

        $stmt = $this->database->prepare($sqlProjectWorkers);
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();

        $workers = [];
        while ($row = $result->fetch_assoc()) {
            $workers[] = $row;
        }

        $details['workers'] = $workers;
        return $details;
    }
    public function getTotalProjectCount(): int
    {
        $sql = "SELECT COUNT(project_id) AS total FROM Projects";
        $result = $this->database->query($sql);
        if ($row = $result->fetch_assoc()) {
            return $row['total'];
        }
        return 0;
    }
    public function getProjectsByFilter(Filter $filter, $page, $pageSize, $order): array
    {
        $projects = array();

        $sql = "SELECT * FROM Projects";

        if ($filter->hasFilters())
            $sql .= $filter->toWhereSQL();

        $offset = ($page - 1) * $pageSize;
        $sql .= " ORDER BY project_id $order";
        $sql .= " LIMIT $offset, $pageSize";

        $result = $this->database->query($sql);

        // page 90 (results 891-900) does not exist, so we get page the actual size of the query with the same filter and then remove 10 from
        if ($result->num_rows <= 0) {
            $realProjectCount = $this->getProjectCountByFilter($filter);
            if ($realProjectCount > 0)
                // pagesize keeps the same but the page
                // only 783 results exists, so we divide 783 by 10 and get 78.3, then we subtract 1 and get 77.3, then we round down to 77 and get 770 results, which is the last page
                return $this->getProjectsByFilter($filter, round($realProjectCount / 10, 0, PHP_ROUND_HALF_DOWN) - 1, $pageSize, $order);
            else
                return $projects;
        }
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
        return $projects;
    }


    // Bearbeiten eines Projekts
    public function updateProject($project_id, $project_name, $start_date, $end_date, $status_id)
    {
        $sql = "UPDATE Projects SET project_name = ?, start_date = ?, end_date = ?, status_id = ? WHERE project_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$project_name, $start_date, $end_date, $status_id, $project_id]);
    }

    // Löschen eines Projekts
    public function deleteProject($project_id)
    {
        $sql = "DELETE FROM Projects WHERE project_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$project_id]);
    }

    // CRUD-Methoden für Zeiterfassungen (Time Entries)

    // Erstellen eines Zeiteintrags
    public function createTimeEntry($user_id, $project_id, $task_id, $start_time, $end_time, $approved_by)
    {

        $sql = "INSERT INTO TimeEntries (user_id, project_id, task_id, start_time, end_time, approved_by) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$user_id, $project_id, $task_id, $start_time, $end_time, $approved_by]);
    }

    // Abrufen eines Zeiteintrags
    public function getTimeEntry($time_entry_id)
    {
        $sql = "SELECT * FROM TimeEntries WHERE time_entry_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$time_entry_id]);
        return $stmt->fetch();
    }

    // Bearbeiten eines Zeiteintrags
    public function updateTimeEntry($time_entry_id, $start_time, $end_time, $description)
    {
        $duration = $end_time->diff($start_time)->format('%H:%I:%S');
        $sql = "UPDATE TimeEntries SET start_time = ?, end_time = ?, duration = ?, description = ? WHERE time_entry_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$start_time->format('Y-m-d H:i:s'), $end_time->format('Y-m-d H:i:s'), $duration, $description, $time_entry_id]);
    }
    //zeiten

    public function getAbsencesTableContent($user_id): array
    {
        $sql="SELECT
            at.type_name as 'Art',
            abs.start_date as 'StartDatum',
            abs.end_date as 'EndDatum',
            abs.absence_id as 'AbsenceID'
        FROM
            Absences abs
        JOIN
            AbsenceTypes at ON abs.type_id = at.type_id
        WHERE
            abs.user_id = ?
            AND abs.start_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 3 MONTH)
        ORDER BY
            abs.start_date DESC";


        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $user_id);  // Bindung des Parameters user_id
        $stmt->execute();
        $result = $stmt->get_result();
        $tableContent = [];

        while ($row = $result->fetch_assoc()) {
            $tableContent[] = $row;
        }

        $stmt->close();  // Schließen des Statements

        return $tableContent;
    }


    // Löschen eines Zeiteintrags
    public function deleteTimeEntry($time_entry_id)
    {
        $sql = "DELETE FROM TimeEntries WHERE time_entry_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$time_entry_id]);
    }

    public function getProjectName($user_id): array
    {
        $sql = "SELECT 
                p.project_id,
                p.project_name AS 'Projektname'
            FROM 
                Projects p
            LEFT JOIN 
                UserRoles ur ON p.project_id = ur.project_id
            LEFT JOIN 
                Users u ON ur.user_id = u.user_id
            WHERE 
                u.user_id = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $user_id);  // Bindung des Parameters user_id
        $stmt->execute();
        $result = $stmt->get_result();
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;  // Speichert nur den Projektnamen
        }
        $stmt->close();  // Schließen des Statements
        return $projects;
    }
    public function getTaskName($user_id): array
    {
        $sql = "SELECT 
                t.task_name AS 'Taskname'
            FROM 
                Tasks t
            INNER JOIN 
                UserRoles ur ON t.project_id = ur.project_id
            INNER JOIN  
                Users u ON ur.user_id = u.user_id
            WHERE 
                u.user_id = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $user_id);  // Bindung des Parameters user_id
        $stmt->execute();
        $result = $stmt->get_result();
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;  // Speichert nur den Projektnamen
        }
        $stmt->close();  // Schließen des Statements
        return $tasks;
    }
    public function getTableContent($user_id): array
    {
        $sql = "SELECT
            te.start_time,
            DATE_FORMAT(te.start_time, '%Y-%m-%d') AS 'Datum',
            DATE_FORMAT(FROM_UNIXTIME(ROUND(UNIX_TIMESTAMP(te.start_time) / (15 * 60)) * (15 * 60)), '%H:%i') AS 'StartZeit',
            DATE_FORMAT(FROM_UNIXTIME(ROUND(UNIX_TIMESTAMP(te.end_time) / (15 * 60)) * (15 * 60)), '%H:%i') AS 'EndZeit',
            te.end_time,
            t.task_name AS 'Taskname',
            p.project_name AS 'Projektname',
            te.time_entry_id AS 'TimeEntryID',
            TIME_FORMAT(SEC_TO_TIME(TIME_TO_SEC(te.end_time) - TIME_TO_SEC(te.start_time) - 7.5 * 3600), '%H:%i') AS 'Saldo'
        FROM
            TimeEntries te
        JOIN
            Tasks t ON te.task_id = t.task_id
        JOIN
            Projects p ON te.project_id = p.project_id
        WHERE
            te.user_id = ?
            AND te.start_time >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)
        ORDER BY
            te.start_time DESC";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $user_id);  // Bindung des Parameters user_id
        $stmt->execute();
        $result = $stmt->get_result();
        $tableContent = [];

        while ($row = $result->fetch_assoc()) {
            $tableContent[] = $row;
        }

        $stmt->close();  // Schließen des Statements

        return $tableContent;
    }

    public function deleteTimeEntryDuplets()
    {
        $sql = "DELETE te1
            FROM TimeEntries te1
            INNER JOIN TimeEntries te2 ON te1.user_id = te2.user_id
                                      AND DATE(te1.start_time) = DATE(te2.start_time)
                                      AND te1.time_entry_id <> te2.time_entry_id
                                      AND te1.start_time < te2.end_time
                                      AND te1.end_time > te2.start_time";

        $stmt = $this->database->prepare($sql);

        if ($stmt === false) {
            // Handle SQL prepare error
            return false;
        }

        $result = $stmt->execute();

        if ($result === false) {
            // Handle SQL execution error
            return false;
        }

        $stmt->close();

        return true;
    }

//    public function TestInsert(){
//        $sql = "INSERT INTO TimeEntries ( start_time) VALUES (2024-06-28 09:00:00)";
//        $stmt = $this->database->prepare($sql);
//        $stmt->execute();
//    }


    // create Table entry if there are no overlaps
    public function createTableEntry($user_id, $project_name, $task_name, $start_time, $end_time, $approved_by)
    {
        // Überprüfen auf Überschneidungen und Dopplungen
        $sql_check = "SELECT COUNT(*) AS count FROM TimeEntries 
                  WHERE user_id = ? 
                  AND DATE(start_time) = DATE(?) 
                  AND (
                      (start_time <= ? AND end_time > ?) OR
                      (start_time < ? AND end_time >= ?) OR
                      (start_time >= ? AND end_time <= ?)
                  )";

        $stmt_check = $this->database->prepare($sql_check);
        $stmt_check->bind_param("isssssss", $user_id, $start_time, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time);
        $stmt_check->execute();
        $stmt_check->store_result(); // Ergebnis der Abfrage im Speicher halten
        $stmt_check->bind_result($count);
        $stmt_check->fetch(); // Ergebnis abrufen und verarbeiten

        if ($count == 0) {
            // Falls keine Überschneidungen gefunden wurden, Daten einfügen
            $sql_insert = "INSERT INTO TimeEntries (user_id, project_id, task_id, start_time, end_time, approved_by) 
                       VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $this->database->prepare($sql_insert);
            $stmt_insert->bind_param("iiisss", $user_id, $project_name, $task_name, $start_time, $end_time, $approved_by);
            $success = $stmt_insert->execute();

            if ($success) {
                $stmt_check->free_result(); // Freigabe der vorherigen Abfrageergebnisse
                return true;
            } else {
                $stmt_check->free_result(); // Freigabe der vorherigen Abfrageergebnisse
                //echo "<script>console.error('Fehler beim Einfügen des Eintrags.');</script>";
                return false;
            }
        } else {
            // Falls Überschneidungen gefunden wurden, Fehlermeldung ausgeben und Einfügen abbrechen
            $stmt_check->free_result(); // Freigabe der vorherigen Abfrageergebnisse
            //echo "<script>alert('Es gibt eine Überschneidung mit einem vorhandenen Eintrag.');</script>";

            return false;

        }

    }

// manager abfrage
    public function getManagerProjects($user_id) {
        $sql = "SELECT p.project_id, p.project_name, p.start_date, p.end_date, ps.status_name
            FROM Projects p
            INNER JOIN UserRoles ur ON p.project_id = ur.project_id
            INNER JOIN ProjectStatus ps ON p.status_id = ps.status_id
            WHERE ur.user_id = ? AND ur.role_id = 7";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $projects = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $projects;
    }

//zugehörige mitarbeiter in dem Projekt

    public function getManagerEmployees($user_id) {
        $sql = "SELECT DISTINCT 
    u.personal_number, 
    u.first_name, 
    u.last_name, 
    u.email, 
    r.role_name, 
    u.entry_date,
    p.project_id,
    p.project_name
FROM 
    Users u
INNER JOIN 
    UserRoles ur ON u.user_id = ur.user_id
INNER JOIN 
    Roles r ON ur.role_id = r.role_id
INNER JOIN 
    Projects p ON ur.project_id = p.project_id
WHERE 
    ur.project_id IN (
        SELECT p.project_id 
        FROM Projects p
        INNER JOIN UserRoles ur ON p.project_id = ur.project_id
        WHERE ur.user_id = ? AND ur.role_id = 7)";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $employees;
    }



    public function getTasksByProject($projectId) {
        $sql = "SELECT task_id, task_name FROM Tasks WHERE project_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getResponsiblePersons($projectId) {
        $sql = "SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) AS name 
            FROM Users u
            JOIN UserRoles ur ON u.user_id = ur.user_id
            WHERE ur.project_id = ? AND u.role_id = 7
        ";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();

        $responsiblePersons = [];
        while ($row = $result->fetch_assoc()) {
            $responsiblePersons[] = $row;
        }

        return $responsiblePersons;
    }
}


?>