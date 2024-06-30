<?php
if (strpos($_SERVER['REQUEST_URI'], 'admin/dashboard.php') !== false) {
    echo $_SERVER['REQUEST_URI'];
    header("Location: ../admin");
    exit();
}?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Work Hours</title>
    <!--<link rel="stylesheet" href="styles.css">-->

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

            row.appendChild(tdProjekt);
            row.appendChild(tdTask);
            row.appendChild(tdDatum);
            row.appendChild(tdBeginn);
            row.appendChild(tdEnde);
            row.appendChild(tdSaldo);
            row.appendChild(tdButton);

            tableBody.appendChild(row);

        }
        // Zeile löschen
        function deleteFunction(button) {
            var result = confirm('Möchten Sie den Eintrag löschen?');
            if (result) {
                var row = button.parentNode.parentNode;
                var table = row.parentNode;
                var saldoMinutes = getMinutesSinceMidnight(document.getElementById(`saldo${row.id}`).innerText);

                gleitzeit = gleitzeit - saldoMinutes;

                document.getElementById("gleitzeit").innerHTML = "Gleitzeit: " + zeitUmform(gleitzeit);
                table.removeChild(row);
            }
        }
        function uberpruefung() {
            console.log("boolDatenredundanz: " + boolDatenredundanz);
        }

        function checkOverlaps() {
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
        }

function giveSaldo(beginTime, endTime){
    const anfangsMinuten = getMinutesSinceMidnight(beginTime);
    const endMinuten = getMinutesSinceMidnight(endTime);

    // Berechnen der Differenz
    var arbeitszeit = endMinuten - anfangsMinuten;

    if (arbeitszeit > 10 * 60) {
        arbeitszeit = 10 * 60;
        window.alert("Eingetragene Arbeitszeit überschreitet 10 Stunden.\n10 Stunden werden angerechnet");
        //Email an Vorgesetzten(?)<--------------
    }
    return  arbeitszeit - standardArbeitszeit;
}
        function addWorkHours() {
            boolDatenredundanz = false;
            let fehlOderUeberstunden;
            var datePickerValue = document.getElementById('datePicker').value;

            if(datePickerValue) {
                if (boolTask === false) {

                    var time1 = roundTime("startTime");
                    var time2;
                    if (!document.getElementById("endTime").value) {
                        window.alert("Ungültige Eingabe - Fehlende End-Zeit");
                    } else {
                        time2 = roundTime("endTime");
                    }

                    fehlOderUeberstunden = giveSaldo(time1,time2);
                    console.log(time1 + ", " + time2)

                    if (getMinutesSinceMidnight(time1) < getMinutesSinceMidnight(time2)) {

                        tabellenZeileErst(
                            document.getElementById("project").value,
                            document.getElementById("tasks").value,
                            document.getElementById("datePicker").value,
                            roundTime("startTime"),
                            roundTime("endTime"),
                            zeitUmform(fehlOderUeberstunden));
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
                        zeitUmform(480),
                        zeitUmform(gleitzeit));

                }

                checkOverlaps();
                if (boolDatenredundanz == true) {
                    window.alert("Ungültige Eingabe - Datendopplung/Datenüberschneidung");
                    var table = document.getElementById("workHoursTable");
                    table.deleteRow(table.rows.length - 1);
                } else {
                    // Gleitzeit Berechnung
                    gleitzeit = gleitzeit + fehlOderUeberstunden;
                    document.getElementById("gleitzeit").innerHTML = "Gleitzeit: " + zeitUmform(gleitzeit);
                }
            } else window.alert("Ungültige Eingabe - Fehlendes Datum");
        }

        // Funktion zum Runden auf die nächstgelegene Viertelstunde
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

            // Zeit aus dem Input-Feld extrahieren
            const [hours, minutes] = timeInput.split(':');

            // Runde die Zeit auf die nächstgelegene Viertelstunde
            const roundedTime = roundToNearestQuarterHour(hours, minutes);

            // Formatiere die gerundete Zeit zurück zu einem String im Format HH:MM
            const roundedTimeString = `${roundedTime.hours}:${roundedTime.minutes}`;

            return `${roundedTimeString}`;
        }

        function testerButton() {
            console.log("------------------------");
            console.log("Gleitzeit: " + gleitzeit);
            console.log("------------------------");

            //Spezialfall: Monatsende:
            var today = new Date();
            //if (today.getDate() === 1 && gleitzeit > 600) {
            gleitzeit = 600;
            var tableBody = document.getElementById("workHoursTable");
            var row = document.createElement("tr");
            row.innerHTML += "<td></td><td></td><td>" + aktuellesDatum() + "</td><td></td><td></td><td></td>";
            var td7 = document.createElement("td");
            td7.innerHTML = zeitUmform(gleitzeit);
            row.appendChild(td7);

            tableBody.appendChild(row);

            //}
        }

        function getMinutesSinceMidnight(time) {
            const [hours, minutes] = time.split(':').map(Number);
            if (hours < 0) {
                return hours * 60 - minutes;
            } else {
                return hours * 60 + minutes;
            }

        }
        // Konvertieren der Differenz in Stunden und Minuten
        function zeitUmform(minutes) {
            var isNegative = minutes < 0;
            var totalMinutes = Math.abs(minutes);
            var hours = Math.floor(totalMinutes / 60);
            var mins = totalMinutes % 60;
            var formattedHours = ('0' + hours).slice(-2);
            var formattedMinutes = ('0' + mins).slice(-2);

            // Negativ Check und Vorzeichen hinzufügen
            return (isNegative ? '-' : '') + formattedHours + ':' + formattedMinutes;
        }

        document.getElementById('project').addEventListener('change', function () {

            var firstSelect = this; // 'this' refers to the select element with id 'project'
            var secondSelect = document.getElementById('tasks');
console.log("krank");
            var selectedOption = firstSelect.options[firstSelect.selectedIndex];
            if (selectedOption.classList.contains('fehlzeitEntsch')) {
                secondSelect.disabled = true;
                boolTask = true;
            } else {
                secondSelect.disabled = false;
                boolTask = false;
            }
        });

        var staterProject;
        var starterTask;
        var starterDate;
        var starterTime;

        function startStopFunction() {
            if (boolStartStop === false){
                staterProject = document.getElementById("project").value;
                starterTask = document.getElementById("tasks").value;
                starterDate = document.getElementById("datePicker").value;
                starterTime = document.getElementById("startTime").value; //aktuelle Zeit + gerundet!
                console.log("start" + starterTime);
                boolStartStop = true;
            }else{

                boolStartStop = false;
                var endTime = document.getElementById("endTime").value;//aktuelle Zeit + gerundet!
                console.log("startTime: "+ starterTime);
                console.log("endTime: "+ endTime);
                tabellenZeileErst(staterProject, starterTask, starterDate, starterTime, endTime, zeitUmform(giveSaldo(starterTime, endTime)));
            }
        }

    </script>
</head>
<body>
<div class="container">
    <h1>Employee Work Hours</h1>

    <!-- Work Hours Form -->
    <form id="workHoursForm">
        <!-- Project & Task Picker -->
        <label for="project"><b>Project</b></label>
        <select name="project" id="project">
            <option id="project_1">project_1</option>
            <option id="project_2">project_2</option>
            <option id="krank" class="fehlzeitEntsch">Krank</option>
            <option id="urlaub" class="fehlzeitEntsch">Urlaub</option>
        </select>
        </br>
        <label for="tasks"><b>Task</b></label>
        <select name="tasks" id="tasks">
            <option id="technischezeichnung">Technische Zeichnung</option>
            <option id="hausbau">Hausbau</option>
        </select>
        </br>
        <!-- Date Picker -->
        <label for="datePicker"><b>Select Date:</b></label>
        <input type="date" id="datePicker" name="datePicker" value="<?php echo date('Y-m-d'); ?>" required>
        </br>
        <!-- Time Picker -->
        <label for="startTime"><b>Start Time:</b></label>
        <input type="time" id="startTime" name="startTime" value="<?php date_default_timezone_set('Europe/Berlin'); echo date('H:i'); ?>" required>

        <button type="button" onclick="startStopFunction()">Start/Stop</button>
        <button type="button" onclick="">Manuelle Eingabe</button>
        <br/>
        <label for="endTime"><b>End Time:</b></label>
        <input type="time" id="endTime" name="endTime" required>

        <button type="button" onclick="addWorkHours()">Arbeitszeit hinzufügen</button>

    </form>
    </br></br></br>

    <p id="gleitzeit">Gleitzeit: </p>

    </br></br></br>
    <!-- Work Hours Table -->
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
        </tbody>
    </table>

</div>

</body>
</html>