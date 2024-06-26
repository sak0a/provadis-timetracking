function switchContentTo(tab) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            loadDynamicScripts(tab);
            document.getElementById('main').innerHTML = xhr.responseText;
            currentTab = tab;
        }
    };
    xhr.send('tab=' + tab + '&ajax=true');
}

function loadDynamicScripts(tab) {
    // Remove existing dynamic scripts if needed
    let oldScripts = document.querySelectorAll('.dynamic-script');
    oldScripts.forEach(function(script) {
        script.parentNode.removeChild(script);
    });
    // Add new scripts based on the tab
    switch (tab) {
        case 'statistics':
            let script = document.createElement('script');
            script.className = 'dynamic-script';
            script.src = '../../assets/js/chartjs.js'; // Assuming the JS file is named after the tab
            document.body.appendChild(script);
            break;
        default:
            break;
    }
}
document.addEventListener('DOMContentLoaded', function() {
    switchContentTo(currentTab)
});