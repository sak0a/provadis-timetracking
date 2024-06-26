function switchContentTo(tab) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('main').innerHTML = xhr.responseText;
        }
    };
    xhr.send('tab=' + tab + '&ajax=true');
}