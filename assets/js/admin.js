function switchContentTo(tab) {
    document.querySelectorAll('.dynamic-script').forEach(script => script.remove());
    console.log("Switching to tab: ", tab); // Debugging
    let xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            loadDynamicScripts(tab);
            document.getElementById('main').innerHTML = xhr.responseText;
            currentTab = tab;
        }
    };
    xhr.send('tab=' + tab + '&ajax=true');
}
function loadDynamicScripts(tab) {
    let scriptSrc = null;
    switch (tab) {
        case 'employees':
            scriptSrc = '../assets/js/admin/employees.js';
            break;
        case 'statistics':
            scriptSrc = '../assets/js/chartjs.js';
            break;
    }
    if (scriptSrc != null) {
        let script = document.createElement('script');
        script.className = 'dynamic-script';
        script.src = scriptSrc;
        document.head.appendChild(script);
    }
}
document.addEventListener('DOMContentLoaded', function () {
    loadDynamicScripts(currentTab);
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
                    <div class="form-group">Benutzer-ID: ${data.user.user_id}</div>
                    <div class="form-group">Personalnummer: ${data.user.personal_number}</div>
                    <div class="form-group">Email: ${data.user.email}</div>
                    <div class="form-group">Geburtsdatum: ${data.user.birthdate}</div>
                    <div class="form-group">Rolle: ${data.user.role_id}</div>
                    <h3>Arbeitsstunden</h3>
                    <div class="form-group">Gesamte Stunden gearbeitet: ${data.hours.total_hours_worked}</div>
                    <div class="form-group">Stunden im letzten Monat: ${data.hours.hours_last_month}</div>
                    <div class="form-group">Stunden in den letzten 3 Monaten: ${data.hours.hours_last_3_months}</div>
                    <div class="form-group">Stunden in den letzten 6 Monaten: ${data.hours.hours_last_6_months}</div>
                    <h3>Projekte und Aufgaben</h3>
                `;

                if (data.projects.length > 0) {
                    data.projects.forEach(project => {
                        detailsDiv.innerHTML += `
                            <h4>Projekt: ${project.project_name}</h4>
                            <div class="form-group">Projekt-ID: ${project.project_id}</div>
                            <div class="form-group">Startdatum: ${project.start_date}</div>
                            <div class="form-group">Enddatum: ${project.end_date}</div>
                            <div class="form-group">Status: ${project.project_status}</div>
                            <div class="form-group">Gearbeitete Stunden an diesem Projekt: ${project.total_hours_on_project}</div>
                            <h5>Aufgaben</h5>
                        `;
                        if (project.task_id) {
                            detailsDiv.innerHTML += `
                                <div class="form-group">Aufgabe-ID: ${project.task_id}</div>
                                <div class="form-group">Aufgabenname: ${project.task_name}</div>
                                <div class="form-group">Erstellt am: ${project.created_at}</div>
                                <div class="form-group">Aktualisiert am: ${project.updated_at}</div>
                            `;
                        } else {
                            detailsDiv.innerHTML += `<div class="form-group">Keine Aufgaben gefunden</div>`;
                        }
                    });
                } else {
                    detailsDiv.innerHTML += `<div class="form-group">Keine Projekte gefunden</div>`;
                }

                document.getElementById('moreDetails').style.display = 'block';
            }
        })
        .catch(error => console.error('Fehler beim Abrufen der Benutzerdetails:', error));
}


function closeModal() {
    document.getElementById('moreDetails').style.display = 'none';
}