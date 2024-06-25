let currentTab = "dashboard";


function switchContentTo(content) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '?ajax=1&f=get_messages&team=' + encodeURIComponent(teams) + '&user=' + encodeURIComponent(user) + '&order=' + order + '&page=' + page, true);
    xhr.onload = function() {
        if (this.status === 200) {
            console.log("RESPONSE:", this.responseText)
            responseData = JSON.parse(this.responseText);
        } else {
            console.log('AJAX error: ' + this.status);
            console.log(this.responseText)
        }
        insertPagination();
        insertTableData();
    };
    xhr.send();
}