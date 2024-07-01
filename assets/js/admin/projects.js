
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
