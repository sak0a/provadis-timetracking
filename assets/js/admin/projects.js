
function showProjectDetails(event, projectId) {
    event.preventDefault();
    fetch(`project_details.php?projectId=${projectId}`)
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
                            <li>${worker.first_name} ${worker.last_name}</li>
                        `).join('')}
                    </ul>
                                            <div class="section"><h2>Statistik</h2></div>
                                               
                        <div class="mdc-layout-grid__inner">                    
                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6-desktop">
                        <h6>1 Monat</h6>
                            <div class="mdc-card">
                            <canvas id="pieChart">Monat</canvas>
                            </div>  
                        </div>                        
                        </div>
                
                
                `;
                document.getElementById('moreDetails').style.display = 'block';
                if (!window.Chart) {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                    script.onload = function() {
                        createCharts(data);
                    };
                    document.head.appendChild(script);
                } else {
                    createCharts(data);
                }
            }
        })
        .catch(error => console.error(error));
}

function createCharts(data) {
    const canvas = document.getElementById('pieChart').getContext('2d');
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