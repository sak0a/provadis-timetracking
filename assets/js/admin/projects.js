function sendUserDataRequest() {
    const currentTime = Date.now();
    if (!(currentTime - lastRequestTime >= throttleDelay)) {
        return;
    }
    const searchId = document.querySelector('input[name=project_search_id]')?.value || '';
    const searchName = document.querySelector('input[name=project_search_name]')?.value || '';
    const searchStartDate = document.querySelector('input[name=project_search_start_date]')?.value || '';
    const searchEndDate = document.querySelector('input[name=project_search_end_date]')?.value || '';
    const searchStatus = document.querySelector('input[name=project_search_status]')?.value || '';
    const searchPlannedTime = document.querySelector('input[name=project_search_planned_time]')?.value || '';
    const orderType = 'desc';
    const page = document.querySelector('input[name=page]')?.value || '1';
    let xhr = new XMLHttpRequest();
    xhr.open('GET', BASE_URL_ADMIN + 'projects.php?f=get_projects' +
        '&s_id=' + searchId +
        '&s_name=' + searchName +
        '&s_start=' + searchStartDate +
        '&s_end=' + searchEndDate +
        '&s_status=' + searchStatus +
        '&s_planned_time=' + searchPlannedTime +
        '&s_order=' + orderType +
        '&s_page=' + page +
        '&ajax=true', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                responseData = JSON.parse(xhr.responseText);
                insertPagination();
                insertTableData();
                lastRequestTime = currentTime;
            }
        }
    };
    xhr.send();
}
function getPaginationClass(pageNumber, totalPages) {
    if (pageNumber === totalPages) {
        return "rounded-r-md";
    } else if (pageNumber === 1) {
        return "rounded-l-md";
    } else {
        return "";
    }
}
function insertPagination() {
    const results = document.getElementById('pagination_results');
    const navigation = document.getElementById('pagination_nav');
    const form = document.getElementById('pagination_form');

    results.getElementsByClassName('start-range')[0].innerHTML = responseData['start_range'];
    results.getElementsByClassName('end-range')[0].innerHTML = responseData['end_range'];
    results.getElementsByClassName('total-results')[0].innerHTML = responseData['total_results'];

    form.querySelector('input[name=page]').setAttribute('max', '' + responseData['total_pages'] + '');

    const currentPage = responseData['current_page'];
    const pageRange = responseData['page_range'];
    const totalPages = responseData['total_pages'];

    navigation.innerHTML = '';
    if (currentPage > 1) {
        const element = document.createElement('a');
        element.setAttribute('data-page', '1');
        element.className = "relative inline-flex items-center rounded-l-md text-gray-400 px-1.5 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0";
        element.innerHTML = '<';
        navigation.appendChild(element);
    }

    for (let i = Math.max(1, currentPage - pageRange); i <= Math.min(currentPage + pageRange, totalPages); i++) {
        const element = document.createElement('a');
        element.setAttribute('data-page', '' + i + '');
        if (i === currentPage) {
            element.className = getPaginationClass(currentPage, totalPages) + " relative z-10 inline-flex px-1.5 items-center bg-[#B68764] text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600";
        } else {
            element.className = " relative hidden items-center text-sm font-normal px-1.5 text-gray-600 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 md:inline-flex";
        }
        if (responseData['total_results'] <= responseData['page_size']) {
            element.classList.add('rounded-md')
        }
        element.innerHTML = '' + i + '';
        navigation.appendChild(element);
    }

    if (currentPage < totalPages) {
        const element = document.createElement('a');
        element.setAttribute('data-page', '' + totalPages + '');
        element.className = " relative inline-flex items-center rounded-r-md px-1.5 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0";
        element.innerHTML = '>';
        navigation.appendChild(element);
    }
}
function insertTableData() {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    for (let i = 0; i < responseData['projects'].length; i++) {
        const row = document.createElement('tr');
        row.style.opacity = '0';
        const project = responseData['projects'][i];

        const projectId = project['id'];
        const projectName = project['name'];
        const projectStartDate = project['start_date'];
        const projectEndDate = project['end_date'];
        const projectStatus = project['status_name'];
        const projectPlannedTime = project['planned_time'];

        row.innerHTML = '' +
            '<td class="pl-2 employee-number" data-id="' + projectId + '">' + projectId + '</td>' +
            '<td class="pl-2">' + projectName + '</td>' +
            '<td class="pl-2">' + projectPlannedTime + '</td>' +
            '<td class="pl-2">' + projectStartDate + '</a></td>' +
            '<td class="pl-2">' + projectEndDate + '</td>' +
            '<td class="pl-2">' + projectStatus + '</td>';
        //'<td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">' +
        //'   <a href="#" class="text-[#B68764] duration-200  transition-colors ease-in-out hover:text-white">Edit<span class="sr-only">, Lindsay Walton</span></a>' +
        //'</td>';
        tableBody.appendChild(row);
        row.querySelector(`.employee-number[data-id="${projectId}"]`).addEventListener('click', function (event) {
            showProjectDetails(event, projectId);
        });
        anime({
            targets: row,
            opacity: 1,
            translateY: [-50, 0], // Slide from 50px above to its original position
            duration: 800, // Duration of animation in milliseconds
            easing: 'easeOutExpo', // Easing function
            delay: i * 50 // Staggered delay for each row
        });
    }
}












function showProjectDetails(event, projectId) {
    event.preventDefault();
    fetch(`${BASE_URL_ADMIN}../api/project_details.php?projectId=${projectId}`)
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



// Animation for fade-in effect
anime({
    targets: document.getElementById('main-container'),
    opacity: [0, 1], // Animate opacity from 0 to 1
    duration: 800, // Duration of animation in milliseconds
    easing: 'easeOutQuad', // Easing function for a smooth fade-in
    delay: 2 * 50 // Staggered delay for each row
});
sendUserDataRequest();
// Event listener for search inputs
searchInputs = document.querySelectorAll('.search-box input');
searchInputs.forEach(function (input) {
    input.addEventListener('input', sendUserDataRequest);
});
// Event listener for pagination links
document.addEventListener('click', function (event) {
    const element = event.target;
    if (element.tagName === 'A' && element.hasAttribute('data-page')) {
        event.preventDefault();
        document.querySelector('input[name=page]').value = element.getAttribute('data-page');
        sendUserDataRequest();
    }
});