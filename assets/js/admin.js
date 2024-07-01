function switchContentTo(tab) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            loadDynamicScripts(tab);
            document.getElementById('main').innerHTML = xhr.responseText;
            currentTab = tab;
        }
    };
    xhr.send('tab=' + tab + '&ajax=true');
}

function loadDynamicScripts(tab) {
    // Remove existing dynamic scripts if needed
    let oldScripts = document.querySelectorAll('.dynamic-script');
    oldScripts.forEach(function(script) {
        script.parentNode.removeChild(script);
    });
    // Add new scripts based on the tab
    switch (tab) {
        case 'statistics':
            let script = document.createElement('script');
            script.className = 'dynamic-script';
            script.src = '../assets/js/chartjs.js'; // Assuming the JS file is named after the tab
            document.body.appendChild(script);
            break;
        default:
            break;
    }
}
document.addEventListener('DOMContentLoaded', function() {
    switchContentTo(currentTab)
});

function showProjectDetails(event, projectId) {
    event.preventDefault();
    fetch(`projects_details.php?projectId=${projectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Fehler:', data.error);
                alert('Fehler: ' + data.error);
            } else {
                let detailsDiv = document.getElementById('projectDetailsContent');
                detailsDiv.innerHTML = `
                    <h2>Projektdetails für ${data.project_name}</h2>
                    <div class="form-group">Projekt-ID: ${data.project_id}</div>
                    <div class="form-group">Startdatum: ${data.start_date}</div>
                    <div class="form-group">Enddatum: ${data.end_date}</div>
                    <div class="form-group">Status: ${data.project_status}</div>
                    <div class="form-group">Verantwortliche: ${data.employees}</div>
                    <div class="form-group">Gesamte Stunden: ${data.total_hours_worked}</div>
                `;
                document.getElementById('moreDetails').style.display = 'block';
            }
        })
        .catch(error => console.error('Fehler beim Abrufen der Projektdetails:', error));
}

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
            <td>Gesamte Stunden gearbeitet</td>
            <td>${data.hours.total_hours_worked}</td>
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
                `;

                document.getElementById('moreDetails').style.display = 'block';
            }
        })
        .catch(error => console.error('Fehler beim Abrufen der Benutzerdetails:', error));
}


function closeModal() {
    document.getElementById('moreDetails').style.display = 'none';
}