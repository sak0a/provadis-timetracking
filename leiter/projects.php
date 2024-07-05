<?php
if (file_exists('../backend/database/Database.php') && file_exists('../backend/database/DatabaseUtil.php')) {
    require_once '../backend/database/Database.php';
    require_once '../backend/database/DatabaseUtil.php';
}
else if (file_exists('backend/database/Database.php') && file_exists('backend/database/DatabaseUtil.php')) {
    require_once 'backend/database/Database.php';
    require_once 'backend/database/DatabaseUtil.php';
}
else {
    die('Required files are missing.');
}

use backend\database\Database;
use backend\database\DatabaseUtil;

// Initialisierung der Datenbankverbindung
$db = Database::initDefault();
// Erstellen eines DatabaseUtil-Objekts
$dbUtil = new DatabaseUtil($db->getConnection());
$user_id = $_SESSION['user']['user_id'];

// Funktion zum Abrufen der Projekte des Managers

// Rufen Sie die Projekte des Managers ab
$projects = $dbUtil->getManagerProjects($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <script src="../assets/js/anime.min.js"></script>
    <script src="../assets/js/global.js"></script>
    <link rel="stylesheet" href="../dist/css/style.purged.css">
    <link rel="stylesheet" href="../dist/css/global.css">
    <link rel="stylesheet" href="../dist/css/admin.css">
</head>
<body>
<div class="container">
    <h1>Projekte des Managers</h1>
    <table id="projectsTable" class="table table-bordered">
        <thead>
        <tr>
            <th>Projekt ID</th>
            <th>Projektname</th>
            <th>Startdatum</th>
            <th>Enddatum</th>
            <th>Status</th>
            <th>Details anzeigen</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($projects as $project): ?>
            <tr>
                <td><?php echo htmlspecialchars($project['project_id']); ?></td>
                <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                <td><?php echo htmlspecialchars($project['start_date']); ?></td>
                <td><?php echo htmlspecialchars($project['end_date']); ?></td>
                <td><?php echo htmlspecialchars($project['status_name']); ?></td>
                <td><button onclick="showProjectDetails(event, <?php echo $project['project_id']; ?>)">Details</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal for more details -->
<div id="moreDetails" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">Close &times;</span>
        <div id="projectDetailsContent"></div>
    </div>
</div>

<div id="userDetailsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeUserDetailsModal()">Close &times;</span>
        <div id="userDetailsContent"></div>
    </div>
</div>

<script>// Set the base URL for admin as needed

    function showProjectDetails(event, project_Id) {
        console.log("dksdkskdskdskdk")
        event.preventDefault();
        fetch(`${BASE_URL_ADMIN}../api/get_project_details.php?project_id=${project_Id}`)
    .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Fehler:', data.error);
                    alert('Fehler: ' + data.error);
                } else {
                    let detailsDiv = document.getElementById('projectDetailsContent');
                    detailsDiv.innerHTML = `
                    <h2>Projektdetails für ${data.getProjectDetails.projektname}</h2>
                    <div class="form-group">Projekt-ID: ${data.getProjectDetails.projekt_id}</div>
                    <div class="form-group">Startdatum: ${data.getProjectDetails.startdatum}</div>
                    <div class="form-group">Enddatum: ${data.getProjectDetails.enddatum}</div>
                    <div class="form-group">Status: ${data.getProjectDetails.status}</div>
                    <div class="form-group">Projektleiter: ${data.getProjectDetails.projektleiter_vorname} ${data.getProjectDetails.projektleiter_nachname}</div>
                    <div class="form-group">Geplannte Zeit: ${data.getProjectDetails.geplannte_zeit} Stunden</div>
                    <div class="form-group">Gesamte Stunden: ${data.getProjectDetails.gesamte_stunden} Stunden</div>
                    <h3>Mitarbeitende:</h3>
                    <ul>
                        ${data.workers.map(worker => `
                            <li><a href="#" onclick="showUserDetails(${worker.personal_number})">${worker.first_name} ${worker.last_name}</a></li>
                        `).join('')}
                    </ul>
                    <div class="section"><h2>Statistik</h2></div>
                    <div class="mdc-layout-grid__inner">
                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                            <h6>1 Monat</h6>
                            <div class="mdc-card">
                                <canvas id="pieChart_pr">Monat</canvas>
                            </div>
                        </div>
                    </div>
                `;
                    document.getElementById('moreDetails').style.display = 'block';
                    if (!window.Chart) {
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                        script.onload = function() {
                            createChart(data);
                        };
                        document.head.appendChild(script);
                    } else {
                        createChart(data);
                    }
                }
            })
            .catch(error => console.error(error));
    }
    function createChart(data) {
        const canvas = document.getElementById('pieChart_pr').getContext('2d');
        const plannedHours = data.getProjectDetails.geplannte_zeit;
        const totalHours = data.getProjectDetails.gesamte_stunden; //die zeiten in % die geplannt waren
        const percentHours= plannedHours/totalHours;
        const percentDiff= 1-percentHours;

        const doughnutPieData = {
            labels: ['Geplannte Zeit', 'Tatsächliche Zeit'],
            datasets: [{
                data: [plannedHours, totalHours],
                backgroundColor: ['#ff6384', '#36a2eb'],
                hoverBackgroundColor: ['#ff6384', '#36a2eb']
            }]
        };

        const doughnutPieOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                animateScale: true,
                animateRotate: true
            }
        };

        new Chart(canvas, {
            type: 'pie',
            data: doughnutPieData,
            options: doughnutPieOptions
        });
    }



    function showUserDetails(personalNumber) {
        fetch(`../api/get_user_details.php?personal_number=${personalNumber}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                } else {
                    let detailsDiv = document.getElementById('userDetailsContent');
                    detailsDiv.innerHTML = `
    <br>
    <details open><summary><h2>Benutzerdetails für ${data.user.first_name} ${data.user.last_name}</h2></summary>
    <p><table class="details-table">
        <tr>
            <th>Attribut</th>
            <th>Wert</th>
        </tr>
        <tr>
            <td>Benutzer-ID</td>
            <td>${data.user.user_id}</td>
        </tr>
        <tr>
            <td>Personalnummer</td>
            <td>${data.user.personal_number}</td>
        </tr>
        <tr>
            <td>Email</td>
            <td>${data.user.email}</td>
        </tr>
        <tr>
            <td>Geburtsdatum</td>
            <td>${data.user.birthdate}</td>
        </tr>
        <tr>
            <td>Rolle</td>
            <td>${data.user.role_id}</td>
        </tr>
    </table></p></details>

        <details>
        <summary>
                    <h3>Arbeitsstunden</h3></summary><p>
    <table class="details-table">
        <tr>
            <th>Zeitraum</th>
            <th>Stunden</th>
        </tr>

        <tr>
            <td>Stunden im letzten Monat</td>
            <td>${data.hours.hours_last_month}</td>
        </tr>
        <tr>
            <td>Stunden in den letzten 3 Monaten</td>
            <td>${data.hours.hours_last_3_months}</td>
        </tr>
        <tr>
            <td>Stunden in den letzten 6 Monaten</td>
            <td>${data.hours.hours_last_6_months}</td>
        </tr>
                <tr>
            <td>Gesamte Stunden gearbeitet</td>
            <td>${data.hours.total_hours_worked}</td>
        </tr>
    </table></p></details><details><summary>
    <h3>Allgemeine Arbeit</h3></summary><p>
                    <table class="details-table">
                            <tr>
            <th>Zeitraum</th>
            <th>Stunden</th>
        </tr>
        <tr>
            <td>Stunden im letzten Monat</td>
            <td>${data.general_hours.hours_last_month_allg_Arbeit}</td>
        </tr>
        <tr>
            <td>Stunden in den letzten 3 Monaten</td>
            <td>${data.general_hours.hours_last_3_months_allg_Arbeit}</td>
        </tr>
        <tr>
            <td>Stunden in den letzten 6 Monaten</td>
            <td>${data.general_hours.hours_last_6_months_allg_Arbeit}</td>
        </tr>
                <tr>
            <td>Stunden Insgesamt</td>
            <td>${data.general_hours.total_hours_allg_Arbeit}</td>
        </tr>
    </table></p></details><details><summary>
<h3>Abwesenheiten</h3></summary><p>
                    <table class="details-table">
                            <tr>
            <th>Zeitraum</th>
            <th>Tagen</th>
        </tr>
        <tr>
            <td>Tagen im letzten Monat</td>
            <td>${data.absences.days_last_month_absences}</td>
        </tr>
        <tr>
            <td>Tagen in den letzten 3 Monaten</td>
            <td>${data.absences.days_last_3_months_absences}</td>
        </tr>
        <tr>
            <td>Tagen in den letzten 6 Monaten</td>
            <td>${data.absences.days_last_6_months_absences}</td>
        </tr>
        <tr>
            <td>Tagen Insgesamt</td>
            <td>${data.absences.total_days_absences}</td>
        </tr>
    </table></p></details><details><summary>

                                 <h3>Projekte und Aufgaben</h3></summary><p>
                    <table class="details-table">
                        <tr>
                            <th>Projekt-ID</th>
                            <th>Projektname</th>
                            <th>Startdatum</th>
                            <th>Enddatum</th>
                            <th>Status</th>
                            <th>Gesamte Stunden</th>
                            <th>Aufgabenname</th>
                        </tr>
                        ${data.projects.map(project => `
                        <tr>
                            <td>${project.project_id}</td>
                            <td>${project.project_name}</td>
                            <td>${project.start_date}</td>
                            <td>${project.end_date}</td>
                            <td>${project.project_status}</td>
                            <td>${project.total_hours_on_project}</td>
                            <td>${project.task_name}</td>
                        </tr>`).join('')}
                    </table></p></details>
<br>

                        <div class="section"><h2>Statistik</h2></div>

                        <div class="mdc-layout-grid__inner">
                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                        <h6>1 Monat</h6>
                            <div class="mdc-card">
                            <canvas id="pieChart">Monat</canvas>
                            </div>
                        </div>

                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                        <h6>3 Monate</h6>
                        <div class="mdc-card">
                            <canvas id="pieChart_3">3 Monate</canvas>
                        </div></div>

                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                        <h6>6 Monate</h6>
                        <div class="mdc-card">
                            <canvas id="pieChart_6">6 Monate</canvas>
                        </div></div>

                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                        <h6>gesamte Zeit</h6>
                            <div class="mdc-card">
                            <canvas id="pieChart_total">Fesamte Zeit</canvas>
                        </div>
                        </div>
                        </div>
                        <div class="mdc-card">
                            <canvas id="myBarChart"></canvas>
                        </div>
                `;
                    document.getElementById('userDetailsModal').style.display = 'block';
// Lade Chart.js, falls nicht geladen
                    if (!window.Chart) {
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                        script.onload = function() {
                            createCharts(data);
                            barChart(data);
                        };
                        document.head.appendChild(script);
                    } else {
                        createCharts(data);
                        barChart(data);
                    }
                }
            })
            .catch(error => console.error(error));
    }

    function createCharts(data) {
        const chartConfigs = [
            {
                elementId: 'pieChart',
                hours: data.hours.hours_last_month,
                generalHours: data.general_hours.hours_last_month_allg_Arbeit,
                absences: data.absences.days_last_month_absences,
            },
            {
                elementId: 'pieChart_3',
                hours: data.hours.hours_last_3_months,
                generalHours: data.general_hours.hours_last_3_months_allg_Arbeit,
                absences: data.absences.days_last_3_months_absences,
            },
            {
                elementId: 'pieChart_6',
                hours: data.hours.hours_last_6_months,
                generalHours: data.general_hours.hours_last_6_months_allg_Arbeit,
                absences: data.absences.days_last_6_months_absences,
            },
            {
                elementId: 'pieChart_total',
                hours: data.hours.total_hours_worked,
                generalHours: data.general_hours.total_hours_allg_Arbeit,
                absences: data.absences.total_days_absences,
            },
        ];

        chartConfigs.forEach(config => {
            const canvas = document.getElementById(config.elementId).getContext('2d');
            const totalHours = config.hours;
            const percent = totalHours / 100;
            const generalHours = config.generalHours / percent;
            const absences = (config.absences * 7.5) / percent;
            const active = 100 - absences - generalHours;

            const doughnutPieData = {
                labels: ['Aktive Arbeit', 'Allgemeine Arbeit', 'Abwesend'],
                datasets: [{
                    data: [active, generalHours, absences],
                    backgroundColor: ['#55eb36', '#36a2eb', '#eb3636'],
                    hoverBackgroundColor: ['#55eb36', '#36a2eb', '#eb3636']
                }]
            };

            const doughnutPieOptions = {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            };

            new Chart(canvas, {
                type: 'pie',
                data: doughnutPieData,
                options: doughnutPieOptions
            });
        });
    }

    function barChart(data){
        const ctx_1 = document.getElementById('myBarChart').getContext('2d');
        const first_month= (1-((data.general_hours.hours_last_month_allg_Arbeit/data.hours.hours_last_month)+((data.absences.days_last_month_absences*7.5)/data.hours.hours_last_month)))*100;
        const three_month= (1-((data.general_hours.hours_last_3_months_allg_Arbeit/data.hours.hours_last_3_months)+((data.absences.days_last_3_months_absences*7.5)/data.hours.hours_last_3_months)))*100;
        const six_month= (1-((data.general_hours.hours_last_6_months_allg_Arbeit/data.hours.hours_last_6_months)+((data.absences.days_last_6_months_absences*7.5)/data.hours.hours_last_6_months)))*100;
        const all_time= (1-((data.general_hours.total_hours_allg_Arbeit/data.hours.total_hours_worked)+((data.absences.total_days_absences*7.5)/data.hours.total_hours_worked)))*100;



        // Define the data for the bar chart
        const data_chart = {
            labels: ['1 Monat', '3 Monate', '6 Monate', 'Gesamte Zeit'],
            datasets: [{
                label: 'Aktive Arbeit in %',
                data: [
                    first_month, three_month, six_month, all_time


                ],
                backgroundColor: [
                    'rgba(99, 255, 109, 0.2)',
                    'rgba(99, 255, 109, 0.2)',
                    'rgba(99, 255, 109, 0.2)',
                    'rgba(99, 255, 109, 0.2)'
                ],
                borderColor: [
                    'rgba(99, 255, 109, 1)',
                    'rgba(99, 255, 109, 1)',
                    'rgba(99, 255, 109, 1)',
                    'rgba(99, 255, 109, 1)'
                ],
                borderWidth: 1
            }]
        };
        const options = {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };
        new Chart(ctx_1, {
            type: 'bar',
            data: data_chart,
            options: options
        });
    }

    function closeUserDetailsModal() {
        document.getElementById('userDetailsModal').style.display = 'none';
    }
    function closeModal() {
        document.getElementById('moreDetails').style.display = 'none';
    }
</script>
</body>
</html>
