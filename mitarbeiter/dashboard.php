<?php
require_once '../backend/database/Database.php';
require_once '../backend/database/DatabaseUtil.php';


use backend\database\Database;
use backend\database\DatabaseUtil;


// Initialisierung der Datenbankverbindung
$db = Database::initDefault();
// Erstellen eines DatabaseUtil-Objekts
$dbUtil = new DatabaseUtil($db->getConnection());

$user_id = $_SESSION['user']['user_id'];
$user = $_SESSION['user'];
$benutzerId=htmlspecialchars($user['user_id']);

$selectedProjectId = isset($_POST['project2']) ? $_POST['project2'] : null;
$responsiblePersons = $selectedProjectId ? $dbUtil->getResponsiblePersons($selectedProjectId) : [];$projectNames = $dbUtil->getProjectName($user_id);
//$dbUtil->deleteTimeEntryDuplets();
$tableContents = $dbUtil->getTableContent($user_id);
$tableGesammtzeit = $dbUtil->getSaldo($user_id);
$absencesTableContents = $dbUtil->getAbsencesTableContent($user_id);
$absTypes = $dbUtil-> getAllAbsenceTypes();

//$dbUtil->TestInsert();





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Work Hours</title>

    <script>
        var gleitzeit = 0;
        const standardArbeitszeit = 450;
        var boolTask = false;
        var boolDatenredundanz = false;
        var boolStartStop = false;

        function tabellenZeileErst(valTdProjekt, valTdTask, valTdDatum, valTdBeginn, valTdEnde, valTdSaldo) {
            var tableBody = document.getElementById("workHoursTable");
            var rowCount = tableBody.rows.length;
            var row = tableBody.insertRow(rowCount);

            var tdProjekt = document.createElement("td");
            var tdTask = document.createElement("td");
            var tdDatum = document.createElement("td");
            var tdBeginn = document.createElement("td");
            var tdEnde = document.createElement("td");
            var tdSaldo = document.createElement("td");
            var tdButton = document.createElement("td");

            tdProjekt.innerHTML = valTdProjekt;
            tdTask.innerHTML = valTdTask;
            tdDatum.innerHTML = valTdDatum;
            tdBeginn.innerHTML = valTdBeginn;
            tdEnde.innerHTML = valTdEnde;
            tdSaldo.innerHTML = valTdSaldo;
            tdButton.innerHTML = "<button onclick='deleteFunction(this)'>X</button>";

            tdDatum.id = "date" + rowCount;
            tdBeginn.id = "startTime" + rowCount;
            tdEnde.id = "endTime" + rowCount;
            tdSaldo.id = "saldo" + rowCount;
            row.id = rowCount;

            tdDatum.value = valTdDatum;

            row.appendChild(tdProjekt);
            row.appendChild(tdTask);
            row.appendChild(tdDatum);
            row.appendChild(tdBeginn);
            row.appendChild(tdEnde);
            row.appendChild(tdSaldo);
            row.appendChild(tdButton);

            tableBody.appendChild(row);
        }

        var datewithin28 = true;

        function deleteFunction(button) {
            // Bestätigungsnachricht
            if (!confirm('Möchten Sie diesen Eintrag wirklich löschen?')) {
                return; // Abbruch, wenn der Benutzer nicht bestätigt
            }

            // Zugriff auf die Zeile, die gelöscht werden soll
            var row = button.closest('tr');
            var id = row.getAttribute('id'); // ID der Zeile, die gelöscht werden soll

            // AJAX-Anfrage, um die Zeile vom Server zu löschen
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/delete_tableEntry.php'); // Hier die URL zum PHP-Skript angeben, das die Löschung durchführt
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Erfolgreiche Antwort vom Server
                    // Hier kannst du weitere Aktionen ausführen, z.B. die Zeile aus der Tabelle entfernen
                    row.remove(); // Entferne die Zeile aus der HTML-Tabelle
                } else {
                    // Fehlerbehandlung
                    alert('Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.');
                }
            };
            xhr.onerror = function() {
                // Fehlerbehandlung bei Verbindungsproblemen
                alert('Es ist ein Verbindungsfehler aufgetreten. Bitte überprüfen Sie Ihre Netzwerkverbindung.');
            };
            xhr.send('id=' + encodeURIComponent(id)); // Sende die ID des zu löschenden Eintrags an das PHP-Skript
        }

        function uberpruefung() {
            console.log("boolDatenredundanz: " + boolDatenredundanz);
        }

        function checkOverlaps(saldo) {
            var table = document.getElementById("workHoursTable");
            var rows = table.rows;
            var timeSlots = [];
            var overlapDetected = false;

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].cells;
                var date = cells[2].innerText;
                var startTime = cells[3].innerText;
                var endTime = cells[4].innerText;

                if (date && startTime && endTime) {
                    var startDateTime = new Date(`${date}T${startTime}`);
                    var endDateTime = new Date(`${date}T${endTime}`);

                    for (var j = 0; j < timeSlots.length; j++) {
                        if (date === timeSlots[j].date &&
                            ((startDateTime >= timeSlots[j].start && startDateTime < timeSlots[j].end) ||
                                (endDateTime > timeSlots[j].start && endDateTime <= timeSlots[j].end) ||
                                (startDateTime <= timeSlots[j].start && endDateTime >= timeSlots[j].end))) {
                            overlapDetected = true;
                            console.log(`Overlap detected between row ${i} and row ${timeSlots[j].index}`);
                        }
                    }

                    timeSlots.push({ date: date, start: startDateTime, end: endDateTime, index: i });
                }
            }

            if (overlapDetected) {
                boolDatenredundanz = true;
            } else {
                boolDatenredundanz = false;
            }
            if (boolDatenredundanz === true) {
                window.alert("Ungültige Eingabe - Datendopplung/Datenüberschneidung");
                table = document.getElementById("workHoursTable");
                table.deleteRow(table.rows.length - 1);
            } else {
                gleitzeit = gleitzeit + saldo;
                document.getElementById("gleitzeit").innerHTML = "Gleitzeit: " + zeitUmform(gleitzeit);
            }
        }

        function giveSaldo(beginTime, endTime) {
            const anfangsMinuten = getMinutesSinceMidnight(beginTime);
            const endMinuten = getMinutesSinceMidnight(endTime);
            var arbeitszeit = endMinuten - anfangsMinuten;

            if (arbeitszeit > 10 * 60) {
                arbeitszeit = 10 * 60;
                window.alert("Eingetragene Arbeitszeit überschreitet 10 Stunden.\n10 Stunden werden angerechnet");
            }
            return arbeitszeit - standardArbeitszeit;
        }

        function addWorkHours() {
            boolDatenredundanz = false;
            let saldo;
            var datePickerValue = document.getElementById('datePicker').value;

            if (datePickerValue && datewithin28 === true) {
                if (boolTask === false) {
                    var time1 = roundTime("startTime");
                    var time2;
                    if (!document.getElementById("endTime").value) {
                        window.alert("Ungültige Eingabe - Fehlende End-Zeit");
                    } else {
                        time2 = roundTime("endTime");
                    }

                    saldo = giveSaldo(time1, time2);
                    console.log(time1 + ", " + time2)

                    if (getMinutesSinceMidnight(time1) < getMinutesSinceMidnight(time2)) {
                        tabellenZeileErst(
                            document.getElementById("project").value,
                            document.getElementById("tasks").value,
                            document.getElementById("datePicker").value,
                            roundTime("startTime"),
                            roundTime("endTime"),
                            zeitUmform(saldo));
                        console.log(document.getElementById("datePicker").value);
                    } else window.alert("Ungültige Eingabe - Zu geringe Arbeiszeit");
                } else {
                    console.log("Krank");
                    tabellenZeileErst(
                        document.getElementById("project").value,
                        "-",
                        document.getElementById("datePicker").value,
                        zeitUmform(480),
                        zeitUmform(standardArbeitszeit + 480),
                        zeitUmform(0));
                }

                checkOverlaps(saldo);
            } else window.alert("Ungültige Eingabe - Fehlerhaftes Datum");
        }

        function roundToNearestQuarterHour(hours, minutes) {
            const totalMinutes = parseInt(hours) * 60 + parseInt(minutes);
            const roundedTotalMinutes = Math.round(totalMinutes / 15) * 15;
            const roundedHours = Math.floor(roundedTotalMinutes / 60);
            const roundedMinutes = roundedTotalMinutes % 60;
            return {
                hours: String(roundedHours).padStart(2, '0'),
                minutes: String(roundedMinutes).padStart(2, '0')
            };
        }

        function roundTime(idOfInputElement) {
            const timeInput = document.getElementById(idOfInputElement).value;
            const [hours, minutes] = timeInput.split(':');
            const roundedTime = roundToNearestQuarterHour(hours, minutes);
            const roundedTimeString = `${roundedTime.hours}:${roundedTime.minutes}`;
            return `${roundedTimeString}`;
        }

        function testerButton() {
            console.log("------------------------");
            console.log("Gleitzeit: " + gleitzeit);
            console.log("------------------------");
            var today = new Date();
            gleitzeit = 600;
            var tableBody = document.getElementById("workHoursTable");
            var row = document.createElement("tr");
            row.innerHTML += "<td></td><td></td><td>" + aktuellesDatum() + "</td><td></td><td></td><td></td>";
            var td7 = document.createElement("td");
            td7.innerHTML = zeitUmform(gleitzeit);
            row.appendChild(td7);
            tableBody.appendChild(row);
        }

        function getMinutesSinceMidnight(time) {
            const [hours, minutes] = time.split(':').map(Number);
            if (hours < 0) {
                return hours * 60 - minutes;
            } else {
                return hours * 60 + minutes;
            }
        }

        function zeitUmform(minutes) {
            var isNegative = minutes < 0;
            var totalMinutes = Math.abs(minutes);
            var hours = Math.floor(totalMinutes / 60);
            var mins = totalMinutes % 60;
            var formattedHours = ('0' + hours).slice(-2);
            var formattedMinutes = ('0' + mins).slice(-2);
            return (isNegative ? '-' : '') + formattedHours + ':' + formattedMinutes;
        }

        function onchangeFehlzeitEntsch() {
            var firstSelect = document.getElementById('project');
            var secondSelectTasks = document.getElementById('tasks');
            var secondSelectStartTime = document.getElementById('startTime');
            var secondSelectEndTime = document.getElementById('endTime');
            var selectedOption = firstSelect.options[firstSelect.selectedIndex];
            if (selectedOption.classList.contains('fehlzeitEntsch')) {
                secondSelectTasks.disabled = true;
                secondSelectStartTime.disabled = true;
                secondSelectEndTime.disabled = true;
                boolTask = true;
            } else {
                secondSelectTasks.disabled = false;
                secondSelectStartTime.disabled = false;
                secondSelectEndTime.disabled = false;
                boolTask = false;
            }
        }

        function contrDate(idOfDate) {
            let selectedDate = new Date(document.getElementById(idOfDate).value);
            let currentDate = new Date();
            let startOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            let endOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);

            if (selectedDate >= startOfMonth && selectedDate <= endOfMonth) {
                console.log("Selected date is within the current month.");
                datewithin28 = true;
            } else if (selectedDate > endOfMonth) {
                console.log("Selected date is in a future month.");
                datewithin28 = true;
            } else {
                console.log("Selected date is more than 28 days ago.");
                datewithin28 = false;
                window.alert("Das ausgewählte Datum liegt im letzten Monat.");
            }
        }
        function getCurrentTime() {
            const now = new Date();
            const hour = now.getHours();
            const minute = now.getMinutes();
            const hourString = timeToString(hour);
            const minuteString = timeToString(minute);

            return `${hourString}:${minuteString}`;
        }


        function timeToString(number) {
            return number.toString().padStart(2, '0');
        }


        // chatgpt
    </script>
</head>
<body>
<div class="container">
    <h1>Employee Work Hours</h1>

    </br></br>

    <button type="button" class="button" onclick="document.getElementById('manuelleEingabe').style.display='block'">Manuelle Eingabe</button>
    <button type="button" class="button" onclick="document.getElementById('absencesEingabe').style.display='block'">Eintrag zur Abwesenheit hinzufügen</button>
    </br></br>


    <table id="absencesTable" class="table table-bordered">
        <thead>
        <tr>
            <th>Art</th>
            <th>Beginn</th>
            <th>Ende</th>
            <th></th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($absencesTableContents as $absencesTableContent): ?>
            <tr>
                <td><?php echo htmlspecialchars($absencesTableContent['Art']); ?></td>
                <td><?php echo htmlspecialchars($absencesTableContent['StartDatum']); ?></td>
                <td><?php echo htmlspecialchars($absencesTableContent['EndDatum']); ?></td>
                <td>
                    <button type="button" class="action-button-abs" data-id="<?php echo htmlspecialchars($absencesTableContent['AbsenceID']); ?>">X</button>
                </td>
            </tr>
        <?php endforeach;
        ?>
        </tbody>
    </table>

    <table id="workHoursTable" class="table table-bordered">
        <thead>
        <tr>
            <th>Projekt</th>
            <th>Task</th>
            <th>Datum</th>
            <th>Beginn</th>
            <th>Ende</th>
            <th>Saldo</th>
            <th></th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($tableContents as $tableContent): ?>
            <tr>
                <td><?php echo htmlspecialchars($tableContent['Projektname']); ?></td>
                <td><?php echo htmlspecialchars($tableContent['Taskname']); ?></td>
                <td><?php echo htmlspecialchars($tableContent['Datum']); ?></td>
                <td><?php echo htmlspecialchars($tableContent['StartZeit']); ?></td>
                <td><?php echo htmlspecialchars($tableContent['EndZeit']); ?></td>
                <td><?php echo htmlspecialchars($tableContent['Saldo']); ?></td>
                <td>
                    <button type="button" class="action-button" data-id="<?php echo htmlspecialchars($tableContent['TimeEntryID']); ?>">X</button>
                </td>
            </tr>
        <?php endforeach;

        echo "Gleitzeit in Industriestunden: " . $tableGesammtzeit;
        ?>

        </tbody>
    </table>

</div>

<div id="manuelleEingabe" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('manuelleEingabe').style.display='none'">Close &times;</span>
        <h2>Manuelle Zeiteingabe</h2>
        <form id="manuelleEingabe2" method="post" action="/api/add_tableEntry.php">
            <input type="hidden" name="user_id2" id="user_id2" value="<?php echo htmlspecialchars($user['user_id']) ?>">
            <div class="form-group">
                <label for="project2"><b>Project</b></label>
                <select name="project2" id="project2">
                    <?php foreach($projectNames as $projectName): ?>
                        <option id="<?php echo htmlspecialchars($projectName['project_id']); ?>" value="<?php echo htmlspecialchars($projectName['project_id']); ?>"><?php echo htmlspecialchars($projectName['Projektname']); ?></option>
                    <?php endforeach; ?>
                </select></div>
            </br>
            <div class="form-group">
                <label for="tasks2"><b>Task</b></label>
                <select name="tasks2" id="tasks2"></select></div>
            </br>
            <div class="form-group">
                <label for="datePicker2"><b>Select Date:</b></label>
                <input type="date" id="datePicker2" name="datePicker2" onchange="contrDate('datePicker')" value="<?php echo date('Y-m-d'); ?>" required></div>
            </br>
            <div class="form-group">
                <label for="startTime2"><b>Start Time:</b></label>
                <input type="time" id="startTime2" name="startTime2" value="<?php date_default_timezone_set('Europe/Berlin'); echo date('H:i'); ?>" required></div>
            <br/><div class="form-group">
                <label for="endTime2"><b>End Time:</b></label>
                <input type="time" id="endTime2" name="endTime2" required></div>
            <br>
            <button type="submit" class="button">Zeit hinzufügen</button>
        </form>
    </div>
</div>

<div id="absencesEingabe" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('absencesEingabe').style.display='none'">Close &times;</span>
        <h2>Manuelle Zeiteingabe</h2>
        <form id="absencesEingabe" method="post" action="/api/add_abcencesEntry.php">
            <input type="hidden" name="user_idAbs" id="user_idAbs" value="<?php echo htmlspecialchars($user['user_id']) ?>">
            <div class="form-group">
                <label for="artAbsences"><b>Project</b></label>
                <select name="artAbsences" id="artAbsences">
                    <?php foreach($absTypes as $absType): ?>
                        <option id="<?php echo htmlspecialchars($absType['type_id']); ?>" value="<?php echo htmlspecialchars($absType['type_id']); ?>"><?php echo htmlspecialchars($absType['type_name']); ?></option>
                    <?php endforeach; ?>
                </select></div>
            </br>
            <div class="form-group">
                <label for="datePickerAbsencesStart"><b>Select Date:</b></label>
                <input type="date" id="datePickerAbsencesStart" name="datePickerAbsencesStart" onchange="contrDate('datePicker')" value="<?php echo date('Y-m-d'); ?>" required></div>
            </br>
            <div class="form-group">
                <label for="datePickerAbsencesEnd"><b>Select Date:</b></label>
                <input type="date" id="datePickerAbsencesEnd" name="datePickerAbsencesEnd" onchange="contrDate('datePicker')" value="<?php echo date('Y-m-d'); ?>" required></div>
            </br>
            <button type="submit" class="button">Eintrag hinzufügen</button>
        </form>
    </div>
</div
</body>
</html>
