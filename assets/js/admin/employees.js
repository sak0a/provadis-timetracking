function sendUserDataRequest() {
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
    xhr.open('GET', BASE_URL_ADMIN + 'employees.php?f=get_emps' +
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
                insertContentTablePagination();
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
            '<td class="pl-2 employee-number" data-pn="' + userPersonalNumber + '"><span>' + userPersonalNumber + '</span><button>Details</button></button></td>' +
            '<td class="pl-2">' + userFirstName + '</td>' +
            '<td class="pl-2">' + userLastName + '</td>' +
            '<td class="pl-2 employee-mail"><a href="mailto:' + userEmail + '">' + userEmail + '</a></td>' +
            '<td class="pl-2">' + userRoleName + '</td>' +
            '<td class="pl-2">' + userEntryDate + '</td>';
            //'<td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">' +
            //'   <a href="#" class="text-[#B68764] duration-200  transition-colors ease-in-out hover:text-white">Edit<span class="sr-only">, Lindsay Walton</span></a>' +
            //'</td>';
        tableBody.appendChild(row);
        row.querySelector(`.employee-number[data-pn="${userPersonalNumber}"]`).addEventListener('click', function (event) {
            handleShowUserDetailsModal(userPersonalNumber);
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

function createChart(time, data) {
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
    let config = [];
    if (time === '1') {
        config = chartConfigs[0];

    } else if (time === '3') {
        config = chartConfigs[1];
    } else if (time === '6') {
        config = chartConfigs[2];
    } else if (time === 'total') {
        config = chartConfigs[3];
    }
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
}

function handleShowUserDetailsModal(userPersonalNumber) {
    let userDetails = [];
    fetch(`${BASE_URL_ADMIN}../api/users_details.php?f=get_user_details&s_pn=${userPersonalNumber}&ajax=true`)
        //fetch(`users_details.php?s_pn=${personalNumber}`)
        .then(response => response.json())
        .then(data => {
            userDetails = data;
        }).catch(error => console.error(error));


    const openButton = document.querySelector(`.employee-number[data-pn="${userPersonalNumber}"]`);

    const closeButton = document.getElementById('close-button');
    const modal = document.getElementById('modal');
    const modalOverlay = document.getElementById('modal-overlay');
    const leftTabButtons = document.querySelectorAll('.left-tab-button');
    const rightTabButtons = document.querySelectorAll('.right-tab-button');
    const leftTabContents = document.querySelectorAll('.tab-content[data-content="1"], .tab-content[data-content="2"], .tab-content[data-content="3"], .tab-content[data-content="4"], .tab-content[data-content="5"]');
    const rightTabContents = document.querySelectorAll('.tab-content[data-content="6"], .tab-content[data-content="7"], .tab-content[data-content="8"], .tab-content[data-content="9"], .tab-content[data-content="10"]');

    function calculateButtonPosition(button) {
        const rect = button.getBoundingClientRect();
        return {
            x: rect.left + window.scrollX,
            y: rect.top + window.scrollY,
            width: rect.width,
            height: rect.height
        };
    }

    function setModalToButtonPosition(button) {
        const position = calculateButtonPosition(button);
        modal.style.width = `${position.width}px`;
        modal.style.height = `${position.height}px`;
        modal.style.left = `${position.x}px`;
        modal.style.top = `${position.y}px`;
    }

    openButton.addEventListener('click', () => {
        setModalToButtonPosition(openButton);

        modal.classList.remove('hidden');
        modalOverlay.classList.remove('hidden');

        setTimeout(() => {
            modal.style.transition = 'transform 0.3s ease, opacity 0.3s ease, width 0.3s ease, height 0.3s ease, left 0.3s ease, top 0.3s ease';
            modal.style.transform = 'scale(1)';
            modal.style.opacity = 1;
            modal.style.width = '80%';
            modal.style.height = '550px';
            modal.style.left = '10%';
            modal.style.top = '-10%';
        }, 10); // Timeout to ensure the initial styles are applied before the transition
    });

    closeButton.addEventListener('click', () => {
        setModalToButtonPosition(openButton);
        modal.style.transform = 'scale(0)';
        modal.style.opacity = 0;

        setTimeout(() => {
            modal.classList.add('hidden');
            modalOverlay.classList.add('hidden');
        }, 300); // Timeout to match the duration of the transition
    });

    modalOverlay.addEventListener('click', () => {
        setModalToButtonPosition(openButton);
        modal.style.transform = 'scale(0)';
        modal.style.opacity = 0;

        setTimeout(() => {
            modal.classList.add('hidden');
            modalOverlay.classList.add('hidden');
        }, 300); // Timeout to match the duration of the transition
    });

    function activateLeftTab(button, content) {
        leftTabButtons.forEach(btn => btn.classList.remove('active-tab'));
        leftTabContents.forEach(cont => cont.classList.add('hidden'));

        button.classList.add('active-tab');
        content.classList.remove('hidden');
    }
    function activateRightTab(button, content) {
        rightTabButtons.forEach(btn => btn.classList.remove('active-tab'));
        rightTabContents.forEach(cont => cont.classList.add('hidden'));

        button.classList.add('active-tab');
        content.classList.remove('hidden');
    }

    function loadTab(tab, content) {

        if (tab === '1') {
            content.innerHTML = `<div class="grid grid-cols-2 grid-rows-2 gap-4">
<div class="col-start-1 row-start-1">
    <h2>Benutzerdetails</h2>
    <table class="min-w-full divide-y divide-gray-200">
                    <tr>
                        <th>Attribut</th>
                        <th>Wert</th>
                    </tr>
                    <tr>
                        <td>Benutzer-ID</td>
                        <td>${userDetails.user.user_id}</td>
                    </tr>
                    <tr>
                        <td>Personalnummer</td>
                        <td>${userDetails.user.personal_number}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>${userDetails.user.email}</td>
                    </tr>
                    <tr>
                        <td>Geburtsdatum</td>
                        <td>${userDetails.user.birthdate}</td>
                    </tr>
                    <tr>
                        <td>Rolle</td>
                        <td>${userDetails.user.role_id}</td>
                    </tr>
                </table>
 </div>
 <div class="col-start-2 row-start-1">
 <h2>Arbeitsstunden</h2>
 <table class="details-table">
                    <tr>
                        <th>Zeitraum</th>
                        <th>Stunden</th>
                    </tr>
            
                    <tr>
                        <td>Stunden im letzten Monat</td>
                        <td>${userDetails.hours.hours_last_month}</td>
                    </tr>
                    <tr>
                        <td>Stunden in den letzten 3 Monaten</td>
                        <td>${userDetails.hours.hours_last_3_months}</td>
                    </tr>
                    <tr>
                        <td>Stunden in den letzten 6 Monaten</td>
                        <td>${userDetails.hours.hours_last_6_months}</td>
                    </tr>
                            <tr>
                        <td>Gesamte Stunden gearbeitet</td>
                        <td>${userDetails.hours.total_hours_worked}</td>
                    </tr>
                </table>
 </div>
 <div class="col-start-1 row-start-2">
    <h2>Allgemeine arbeit</h2>
  <table class="details-table">
                    <tr>
                        <th>Zeitraum</th>
                        <th>Stunden</th>
                    </tr>
                    <tr>
                        <td>Stunden im letzten Monat</td>
                        <td>${userDetails.general_hours.hours_last_month_allg_Arbeit}</td>
                    </tr>
                    <tr>
                        <td>Stunden in den letzten 3 Monaten</td>
                        <td>${userDetails.general_hours.hours_last_3_months_allg_Arbeit}</td>
                    </tr>
                    <tr>
                        <td>Stunden in den letzten 6 Monaten</td>
                        <td>${userDetails.general_hours.hours_last_6_months_allg_Arbeit}</td>
                    </tr>
                            <tr>
                        <td>Stunden Insgesamt</td>
                        <td>${userDetails.general_hours.total_hours_allg_Arbeit}</td>
                    </tr>
                 </table>
 </div>
 <div>
        <h2>Abwesenheiten</h2>
     <table class="details-table">
                    <tr>
                        <th>Zeitraum</th>
                        <th>Tagen</th>
                    </tr>
                    <tr>
                        <td>Tagen im letzten Monat</td>
                        <td>${userDetails.absences.days_last_month_absences}</td>
                    </tr>
                    <tr>
                        <td>Tagen in den letzten 3 Monaten</td>
                        <td>${userDetails.absences.days_last_3_months_absences}</td>
                    </tr>
                    <tr>
                        <td>Tagen in den letzten 6 Monaten</td>
                        <td>${userDetails.absences.days_last_6_months_absences}</td>
                    </tr>
                    <tr>
                        <td>Tagen Insgesamt</td>
                        <td>${userDetails.absences.total_days_absences}</td>
                    </tr>
                </table>
 </div>
</div>
    
                
                `;
        } else if (tab === '5') {
            content.innerHTML = `
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
                        ${userDetails.projects.map(project => `
                        <tr>
                            <td>${project.project_id}</td>
                            <td>${project.project_name}</td>
                            <td>${project.start_date}</td>
                            <td>${project.end_date}</td>
                            <td>${project.project_status}</td>
                            <td>${project.total_hours_on_project}</td>
                            <td>${project.task_name}</td>
                        </tr>`).join('')}
                    </table>`;
        }  else if (tab === '6') {
            content.innerHTML = `<canvas id="pieChart">Monat</canvas>`;
            createChart('1', userDetails);
        } else if (tab === '7') {
            content.innerHTML = `<canvas id="pieChart_3">Monat</canvas>`;
            createChart('3', userDetails);
        } else if (tab === '8') {
            content.innerHTML = `<canvas id="pieChart_6">Monat</canvas>`;
            createChart('6', userDetails);
        } else if (tab === '9') {
            content.innerHTML = `<canvas id="pieChart_total">Monat</canvas>`;
            createChart('total', userDetails);
        } else if (tab === '10') {
            content.innerHTML = `<canvas id="myBarChart">Monat</canvas>`;
            createBarChart(userDetails, content);
        }
    }




    leftTabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tab = button.getAttribute('data-tab');
            const content = document.querySelector(`.tab-content[data-content="${tab}"]`);
            loadTab(tab, content);
            activateLeftTab(button, content);
        });
    });

    rightTabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tab = button.getAttribute('data-tab');
            const content = document.querySelector(`.tab-content[data-content="${tab}"]`);
            loadTab(tab, content);
            activateRightTab(button, content);
        });
    });

    // <!-- ACTIVATE DEFAULT TABS ON MODAL OPEN -->

    setTimeout(() => {
        // Benutzerdetails
        loadTab('1', document.querySelector('.tab-content[data-content="1"]'));
        activateLeftTab(leftTabButtons[0], document.querySelector('.tab-content[data-content="1"]'));
        // 1 Monats Statistik
        loadTab('6', document.querySelector('.tab-content[data-content="6"]'));
        activateRightTab(rightTabButtons[0], document.querySelector('.tab-content[data-content="6"]'));
    }, 500);
}

function createBarChart(data){
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

// Animation for fade-in effect
anime({
    targets: document.getElementById('main-container'),
    opacity: [0, 1], // Animate opacity from 0 to 1
    duration: 800, // Duration of animation in milliseconds
    easing: 'easeOutQuad', // Easing function for a smooth fade-in
    delay: 2 * 50 // Staggered delay for each row
});
sendUserDataRequest();
handleContentTableInputs()