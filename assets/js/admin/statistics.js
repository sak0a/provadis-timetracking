    fetch(`all_projects_details.php`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
            } else {
                let detailsDiv = document.getElementById('projectDetailsContent');
                detailsDiv.innerHTML = data.map(project => `
                        <div class="statistic_box">
                        <details><summary><h1>Projektdetails für ${project['Projektname']}</h1></summary>
                        <p><table class="details-table">
                            <tr>
                                <th>Attribut</th>
                                <th>Wert</th>
                            </tr>
                            <tr>
                                <td>Projekt-ID</td>
                                <td>${project['Projekt-ID']}</td>
                            </tr>
                            <tr>
                                <td>Projektleiter</td>
                                <td>${project['Projektleiter']}</td>
                            </tr>
                            <tr>
                                <td>Startdatum</td>
                                <td>${project['Startdatum']}</td>
                            </tr>
                            <tr>
                                <td>Enddatum</td>
                                <td>${project['Enddatum']}</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>${project['Status']}</td>
                            </tr>
                            <tr>
                                <td>Geplante Zeit</td>
                                <td>${project['Geplannte Zeit']}</td>
                            </tr>
                            <tr>
                                <td>Gesamte Stunden</td>
                                <td>${project['Gesamte Stunden']}</td>
                            </tr>
                        </table></p>
                        <div class="mdc-card">
                            <canvas id="pieChart_${project['Projekt-ID']}"></canvas>
                        </div>
                        </details>
                        </div>
                `).join('');

                // Lade Chart.js, falls nicht geladen
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

function createCharts(data) {
    data.forEach(project => {
        const canvas = document.getElementById(`pieChart_${project['Projekt-ID']}`).getContext('2d');
        const plannedHours = project['Geplannte Zeit'];
        const totalHours = project['Gesamte Stunden'];
        const percentHours = (plannedHours / totalHours) * 100;
        const percentDiff = 100 - percentHours;

        const doughnutPieData = {
            labels: ['Geplannte Zeit', 'Tatsächliche Zeit'],
            datasets: [{
                data: [percentHours, percentDiff],
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
    });
}
