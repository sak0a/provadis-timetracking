
function showProjectDetails(event, projectId) {
    event.preventDefault();
    fetch(`/admin/project_details.php?projectId=${projectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Fehler:', data.error);
                alert('Fehler: ' + data.error);
            } else {
                let detailsDiv = document.getElementById('projectDetailsContent');
                detailsDiv.innerHTML = `
                    <h2>Projektdetails für ${data['Projektname']}</h2>
                    <div class="form-group">Projekt-ID: ${data['Projekt-ID']}</div>
                    <div class="form-group">Startdatum: ${data['Startdatum']}</div>
                    <div class="form-group">Enddatum: ${data['Enddatum']}</div>
                    <div class="form-group">Status: ${data['Status']}</div>
                    <div class="form-group">Projektleiter: ${data['Projektleiter_Vorname']} ${data['Projektleiter_Nachname']}</div>
                    <div class="form-group">Geplannte Zeit: ${data['Geplannte Zeit']} Stunden</div>
                    <div class="form-group">Gesamte Stunden: ${data['Gesamte Stunden']} Stunden</div>
                `;
                document.getElementById('moreDetails').style.display = 'block';
            }
        })
        .catch(error => console.error('Fehler beim Abrufen der Projektdetails:', error));
}