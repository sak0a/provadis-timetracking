<?php
if (file_exists('../backend/database/Database.php') && file_exists('../backend/database/DatabaseUtil.php')) {
    require_once '../backend/database/Database.php';
    require_once '../backend/database/DatabaseUtil.php';
} else if (file_exists('backend/database/Database.php') && file_exists('backend/database/DatabaseUtil.php')) {
    require_once 'backend/database/Database.php';
    require_once 'backend/database/DatabaseUtil.php';
} else {
    die('Required files are missing.');
}

use backend\database\Database;
use backend\database\DatabaseUtil;

// Initialisierung der Datenbankverbindung
$db = Database::initDefault();
$dbUtil = new DatabaseUtil($db->getConnection());

// Angenommen, der Benutzer ist bereits in der Sitzung gespeichert
$user_id = $_SESSION['user']['user_id'];

// Rufen Sie die Mitarbeiter des Managers ab
$employees = $dbUtil->getManagerEmployees($user_id);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Mitarbeiter</title>
</head>
<body>


<div class="container" id="leiter_employee_table">
    <h1>Mitarbeiter des Managers</h1>
    <table id="employeesTable" class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Personalnummer</th>
                <th>E-Mail</th>
                <th>Projekt ID</th>
                <th>Projektname</th>
                <th>Stunden freigeben</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($employees as $employee): ?>
        <tr>
            <td>
                <button class="btn btn-primary" onclick="showUserDetails(<?php echo htmlspecialchars($employee['personal_number']); ?>)">
                    <?php echo htmlspecialchars($employee['first_name']) . ' ' . htmlspecialchars($employee['last_name']); ?>
                </button>
            </td>
            <td><?php echo htmlspecialchars($employee['personal_number']); ?></td>
            <td><?php echo htmlspecialchars($employee['email']); ?></td>
            <td><?php echo htmlspecialchars($employee['project_id']); ?></td>
            <td><?php echo htmlspecialchars($employee['project_name']); ?></td>
            <td>
            <button class="details-btn" style="background: #0F2D3B; border-radius: 1000px; padding: 4px 12px; color: #F7CD45;" onclick="showHoursModal(<?php echo htmlspecialchars($employee['personal_number']); ?>, <?php echo htmlspecialchars($employee['project_id']); ?>, <?php echo $user_id; ?>)">
            Stunden Freigeben
            </button>
        
    </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div id="userDetailsModal" class="modal">
    <div class="modal-content" style="width: 800px !important;">
        <span class="close" onclick="closeUserDetailsModal()">Close &times;</span>
        <div id="userDetailsContent"></div>
    </div>
</div>

<div id="hoursModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeHoursModal()">Close &times;</span>
        <div id="hoursContent"></div>
    </div>
</div>
</body>
<script>   




function showHoursModal(personalNumber, projectId, managerId) {
    if (projectId === undefined || projectId === null) {
        console.error('projectId is not defined');
        alert('Fehler: Projekt-ID ist nicht definiert');
        return;
    }

    let url = `${BASE_URL_ADMIN}../api/get_user_details.php?personal_number=${personalNumber}&project_id=${projectId}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Fehler:', data.error);
                alert('Fehler: ' + data.error);
            } else {
                const totalHoursLastMonth = parseFloat(data.hours.hours_last_month);
                const generalHoursLastMonth = parseFloat(data.general_hours.hours_last_month_allg_Arbeit);
                const absencesLastMonth = parseFloat(data.absences.days_last_month_absences) * 7.5;
                const hoursToBeApproved = totalHoursLastMonth - generalHoursLastMonth;

                const totalHoursToBeApproved = data.approved_null ? parseFloat(data.approved_null.total_hours_on_project_last_month) : 0;

                let hoursDiv = document.getElementById('hoursContent');
                hoursDiv.innerHTML = `
                    <h2>Stunden Freigeben für ${data.user.first_name} ${data.user.last_name}</h2>
                    <div class="form-group">Gesamte gearbeitete Stunden im letzten Monat: ${totalHoursLastMonth}</div>
                    <div class="form-group">Allgemeine Arbeitsstunden im letzten Monat: ${generalHoursLastMonth}</div>
                    <div class="form-group">Abwesenheitstage im letzten Monat (in Stunden): ${absencesLastMonth}</div>
                    <div class="form-group">Stunden zur Freigabe: ${hoursToBeApproved}</div>
                    <div class="form-group">Freigegebene Stunden (nicht genehmigt): ${totalHoursToBeApproved}</div>
                    <button class="btn btn-success" onclick="approveHours(${data.user.user_id}, ${projectId}, ${managerId})">Stunden Freigeben</button>
                `;
                document.getElementById('hoursModal').style.display = 'block';
            }
        })
        .catch(error => console.error(error));
}

showHoursModal(personalNumber, projectId, managerId);

function approveHours(userId, projectId, managerId) {
    fetch(`${BASE_URL_ADMIN}../api/approve_hours.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `user_id=${userId}&project_id=${projectId}&manager_id=${managerId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Stunden erfolgreich freigegeben');
            document.getElementById('hoursModal').style.display = 'none';
            // Optional: Aktualisieren Sie die Ansicht oder die Daten hier
        } else {
            console.error('Fehler:', data.error);
            alert('Fehler: ' + data.error);
        }
    })
    .catch(error => console.error('Fehler:', error));
}


    function closeHoursModal() {
        document.getElementById('hoursModal').style.display = 'none';
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
</html>
