/**
 * Live Content Switch in PHP with JavaScript
 * @param tab - Content Container to switch to
 */
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

/**
 * Dynamically Loads JavaScript Files
 * @param tab - Content Container to use in
 */
function loadDynamicScripts(tab) {
    let scriptSrc = null;
    let script = null;
    switch (tab) {
        case 'employees':
            createDynamicScript('https://cdn.jsdelivr.net/npm/chart.js')
            createDynamicScript('../assets/js/admin/employees.js');
            break;
        case 'statistics':
            createDynamicScript('https://cdn.jsdelivr.net/npm/chart.js')
            createDynamicScript('../assets/js/admin/statistics.js');
            break;
        case 'projects':
            createDynamicScript('https://cdn.jsdelivr.net/npm/chart.js')
            createDynamicScript('../assets/js/admin/projects.js');
            break;
        case 'dashboard':
            createDynamicScript('https://cdn.jsdelivr.net/npm/chart.js')
            createDynamicScript('../assets/js/admin/dashboard.js');
            break;
    }
}

/**
 * Create a dynamic script element in the head of the DOM
 * @param src - Source URL/Path of the script
 */
function createDynamicScript(src) {
    let script = document.createElement('script');
    script.className = 'dynamic-script';
    script.src = src;
    document.head.appendChild(script);
}

/**
 * Handling of the Search Fields above a Content Table and the Pagination below
 */
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

/**
 * Checks whether the Button is on the left or right side of the pagination, and adds the appropriate class
 * @param pageNumber - Page number to request too
 * @param totalPages - Total number of pages available
 * @returns {string} - Class name for the pagination button
 */
function getPaginationClass(pageNumber, totalPages) {
    if (pageNumber === totalPages) {
        return "rounded-r-full";
    } else if (pageNumber === 1) {
        return "rounded-l-full";
    } else {
        return "";
    }
}

/**
 * Inserts the Pagination for the Content Table into the DOM
 * @description Sets apropriate attributes and classes for the Pagination
 * @param responseData - Data from the current AJAX Request, variable is set in the global scope
 */
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
        element.className = "pagination_btn_arrow rounded-l-full";
        element.innerHTML = '<';
        navigation.appendChild(element);
    }

    for (let i = Math.max(1, currentPage - pageRange); i <= Math.min(currentPage + pageRange, totalPages); i++) {
        const element = document.createElement('a');
        element.setAttribute('data-page', '' + i + '');
        if (i === currentPage) {
            element.className = getPaginationClass(currentPage, totalPages) + " pagination_btn_selected";
        } else {
            element.className = " pagination_btn_unselected";
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
        element.className = "pagination_btn_arrow rounded-r-full";
        element.innerHTML = '>';
        navigation.appendChild(element);
    }
}

/**
 * Calculates the Button Position based on the Button Element used to open a Modal
 * @param button - HTML Button Element
 * @returns {{x: *, width , y: *, height}}
 */
function calculateButtonPosition(button) {
    const rect = button.getBoundingClientRect();
    return {
        x: rect.left + window.scrollX,
        y: rect.top + window.scrollY,
        width: rect.width,
        height: rect.height
    };
}

/**
 * Sets the Modal to the Position of the Button that opened it
 * @param button - HTML Button Element
 * @param modal - HTML Modal Element
 */
function setModalToButtonPosition(button, modal) {
    const position = calculateButtonPosition(button);
    modal.style.width = `${position.width}px`;
    modal.style.height = `${position.height}px`;
    modal.style.left = `${position.x}px`;
    modal.style.top = `${position.y}px`;
}

/**
 *
 * @param openButton - HTML Button Element to open the Modal
 * @param modal - HTML Modal Element
 * @param modalOverlay - HTML Modal Background Overlay Element
 * @param closeButton - HTML Button Element to close the Modal
 * @param modalAttributes - Object with the Modal's attributes (width, height, left, top)
 */
function handleModalBaseFunctionality(openButton, modal, modalOverlay, closeButton, modalAttributes) {
    openButton.addEventListener('click', () => {
        setModalToButtonPosition(openButton, modal);

        modal.classList.remove('hidden');
        modalOverlay.classList.remove('hidden');

        setTimeout(() => {
            modal.style.transition = 'transform 0.3s ease, opacity 0.3s ease, width 0.3s ease, height 0.3s ease, left 0.3s ease, top 0.3s ease';
            modal.style.transform = 'scale(1)';
            modal.style.opacity = 1;
            modal.style.width = modalAttributes.width;
            modal.style.height = modalAttributes.height;
            modal.style.left = modalAttributes.left;
            modal.style.top = modalAttributes.top;
        }, 10); // Timeout to ensure the initial styles are applied before the transition
    });

    closeButton.addEventListener('click', () => {
        setModalToButtonPosition(openButton, modal);
        modal.style.transform = 'scale(0)';
        modal.style.opacity = 0;

        setTimeout(() => {
            modal.classList.add('hidden');
            modalOverlay.classList.add('hidden');
        }, 300); // Timeout to match the duration of the transition
    });

    modalOverlay.addEventListener('click', () => {
        setModalToButtonPosition(openButton, modal);
        modal.style.transform = 'scale(0)';
        modal.style.opacity = 0;

        setTimeout(() => {
            modal.classList.add('hidden');
            modalOverlay.classList.add('hidden');
        }, 300); // Timeout to match the duration of the transition
    });
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