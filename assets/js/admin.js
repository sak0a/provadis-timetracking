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
            createDynamicScript('../assets/js/admin/employees.js');
            createDynamicScript('../assets/js/chartjs.js')
            break;
        case 'statistics':
            createDynamicScript('../assets/js/chartjs.js');
            break;
    }
}

function createDynamicScript(src) {
    let script = document.createElement('script');
    script.className = 'dynamic-script';
    script.src = src;
    document.head.appendChild(script);
}

document.addEventListener('DOMContentLoaded', function () {
    loadDynamicScripts(currentTab);
});

function closeModal() {
    document.getElementById('moreDetails').style.display = 'none';
}