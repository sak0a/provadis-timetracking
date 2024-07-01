function sendRequest() {
    const currentTime = Date.now();
    if (!(currentTime - lastRequestTime >= throttleDelay)) {
        return;
    }
    const searchPersonalNumber = document.querySelector('input[name=emp_search_pn]')?.value || '';
    const searchFirstName = document.querySelector('input[name=emp_search_fn]')?.value || '';
    const searchLastName = document.querySelector('input[name=emp_search_ln]')?.value || '';
    const searchEmail = document.querySelector('input[name=emp_search_email]')?.value || '';
    const searchRole = document.querySelector('input[name=emp_search_role]')?.value || '';
    const searchEntryDate = document.querySelector('input[name=emp_search_entrydate]')?.value || '';
    const orderType = 'desc';
    const page = document.querySelector('input[name=page]')?.value || '1';
    let xhr = new XMLHttpRequest();
    xhr.open('GET', '/admin/employees.php?f=get_emps' +
        '&s_pn=' + searchPersonalNumber +
        '&s_fn=' + searchFirstName +
        '&s_ln=' + searchLastName +
        '&s_email=' + searchEmail +
        '&s_role=' + searchRole +
        '&s_entry=' + searchEntryDate +
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

    for (let i = 0; i < responseData['users'].length; i++) {
        const row = document.createElement('tr');
        row.style.opacity = '0';
        const user = responseData['users'][i];

        const userPersonalNumber = user['personal_number'];
        const userRoleName = user['role_name'];
        const userFirstName = user['first_name'];
        const userLastName = user['last_name'];
        const userEmail = user['email'];
        const userEntryDate = user['entry_date'];

        row.innerHTML = '' +
            '<td class="pl-2 employee-number" data-pn="' + userPersonalNumber + '">' + userPersonalNumber + '</td>' +
            '<td class="pl-2 transition-colors duration-250 hover:text-[#B68764]">' + userFirstName + '</td>' +
            '<td class="pl-2">' + userLastName + '</td>' +
            '<td class="pl-2">' + userEmail + '</td>' +
            '<td class="pl-2">' + userRoleName + '</td>' +
            '<td class="pl-2">' + userEntryDate + '</td>';
            //'<td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">' +
            //'   <a href="#" class="text-[#B68764] duration-200  transition-colors ease-in-out hover:text-white">Edit<span class="sr-only">, Lindsay Walton</span></a>' +
            //'</td>';
        tableBody.appendChild(row);
        row.querySelector(`.employee-number[data-pn="${userPersonalNumber}"]`).addEventListener('click', function (event) {
            console.log("MODAL FOR " + userPersonalNumber)
            showUserDetails(event, userPersonalNumber);
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

/**
 * Show User Details in Modal
 * @param event
 * @param personalNumber
 */
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


                        <div class="section">
                            <h2>Statistik letzter Monat</h2>
                        </div>                        
                        <div class="mdc-card">
                            <canvas id="pieChart"></canvas>  
                        </div>

                        <div class="section">
                            <h2>Statistik 3 Monaten</h2>
                        </div>                        
                        <div class="mdc-card">
                            <canvas id="pieChart_3"></canvas>  
                        </div>

                        <div class="section">
                            <h2>Statistik 6 Monaten</h2>
                        </div>                        
                        <div class="mdc-card">
                            <canvas id="pieChart_6"></canvas>  
                        </div>

                        <div class="section">
                            <h2>Statistik gesamte Zeit</h2>
                        </div>                        
                        <div class="mdc-card">
                            <canvas id="pieChart_total"></canvas>  
                        </div>

                
                `;

                document.getElementById('moreDetails').style.display = 'block';

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
        .catch(error => console.error('Fehler beim Abrufen der Benutzerdetails:', error));
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
                backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe'],
                hoverBackgroundColor: ['#ff6384', '#36a2eb', '#cc65fe']
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





// Animation for fade-in effect
anime({
    targets: document.getElementById('main-container'),
    opacity: [0, 1], // Animate opacity from 0 to 1
    duration: 800, // Duration of animation in milliseconds
    easing: 'easeOutQuad', // Easing function for a smooth fade-in
    delay: 2 * 50 // Staggered delay for each row
});
sendRequest();
// Event listener for search inputs
searchInputs = document.querySelectorAll('.search-box input');
searchInputs.forEach(function (input) {
    input.addEventListener('input', sendRequest);
});
// Event listener for pagination links
document.addEventListener('click', function (event) {
    const element = event.target;
    if (element.tagName === 'A' && element.hasAttribute('data-page')) {
        event.preventDefault();
        document.querySelector('input[name=page]').value = element.getAttribute('data-page');
        sendRequest();
    }
});
