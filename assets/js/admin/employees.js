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
function showUserDetails(event, personalNumber) {
    fetch('/admin/users_details.php?f=get_user_details&s_pn=' + personalNumber + '&ajax=true')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
            } else {
                let detailsDiv = document.getElementById('userDetailsContent');
                detailsDiv.innerHTML = `
                    <h2>Benutzerdetails für ${data.user.first_name} ${data.user.last_name}</h2>
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
        .catch(error => console.error(error));
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
