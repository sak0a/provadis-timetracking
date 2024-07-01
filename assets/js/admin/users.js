function showUserDetails(event, userId) {
    event.preventDefault();
    fetch(`users_details.php?userId=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Fehler:', data.error);
                alert('Fehler: ' + data.error);
            } else {
                let detailsDiv = document.getElementById('userDetailsContent');
                detailsDiv.innerHTML = `
                    <script src="chartjs.js"></script>
    <script src="../vendors/js/vendor.bundle.base.js"></script>
    <script src="../vendors/chartjs/Chart.min.js"></script>

                
<h2>Benutzerdetails für ${data.user.first_name} ${data.user.last_name}</h2>
    <table class="details-table">
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
    </table>
                    <h3>Arbeitsstunden</h3>
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
    </table>
    <h3>Allgemeine Arbeit</h3>
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
    </table>
<h3>Abwesenheiten</h3>
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
    </table>

                                 <h3>Projekte und Aufgaben</h3>
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
                    </table>



    
    <!-- Benutzerverwaltung -->
<div class="container">

    <div class="section">
      <h2>Statistik</h2>
    </div>



    <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
      <div class="mdc-card">
        <h6 class="card-title">Pie chart</h6>
        <canvas id="pieChart"></canvas>

      </div>

  </div>
                `;

                document.getElementById('moreDetails').style.display = 'block';
            }
        })
        .catch(error => console.error('Fehler beim Abrufen der Benutzerdetails:', error));
}