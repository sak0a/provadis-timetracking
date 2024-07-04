function switchContentTo(tab) {
    document.querySelectorAll('.dynamic-script').forEach(script => script.remove());
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
    let script = null;
    switch (tab) {
        case 'employees':
            createDynamicScript('https://cdn.jsdelivr.net/npm/chart.js')
            createDynamicScript('../assets/js/admin/employees.js');
            break;
        case 'statistics':
            createDynamicScript('../assets/js/admin/statistics.js');
            break;
        case 'projects':
            createDynamicScript('../assets/js/admin/projects.js');
        break;
        case 'dashboard':
            createDynamicScript('../assets/js/admin/dashboard.js');
            break;
    }
}

function createDynamicScript(src) {
    let script = document.createElement('script');
    script.className = 'dynamic-script';
    script.src = src;
    document.head.appendChild(script);
}


function handleContentTableInputs() {
    const searchInputs = document.querySelectorAll('.search-box input');
    searchInputs.forEach(function (input) {
        input.addEventListener('input', sendUserDataRequest);
    });
    document.addEventListener('click', function (event) {
        const element = event.target;
        if (element.tagName === 'A' && element.hasAttribute('data-page')) {
            event.preventDefault();
            document.querySelector('input[name=page]').value = element.getAttribute('data-page');
            sendUserDataRequest();
        }
    });
}
function insertContentTablePagination() {
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

document.addEventListener('DOMContentLoaded', function () {
    loadDynamicScripts(currentTab);
});










function closeModal() {
    document.getElementById('moreDetails').style.display = 'none';
}
function closeModalByExit() {document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});} 
closeModalByExit();