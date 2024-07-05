// Funktion zum Laden der Aufgaben
function fetchTasks(projectId, tasksElementId) {
    fetch(`../api/get_tasks.php?projectId=${projectId}`)
        .then(response => response.json())
        .then(data => {
            var tasksSelect = document.getElementById(tasksElementId);
            tasksSelect.innerHTML = ''; // Clear existing options
            if (data.error) {
                console.error('Fehler:', data.error);
                alert('Fehler: ' + data.error);
            } else {
                data.forEach(task => {
                    var option = document.createElement('option');
                    option.value = task.task_id;
                    option.text = task.task_name;
                    tasksSelect.add(option);
                });
            }
        })
        .catch(error => console.error('Fehler beim Abrufen der Aufgaben:', error));
}

// Event-Listener für die Projekt-Auswahl
function  dksjodjsjds() {
    document.getElementById('project2').addEventListener('change', function() {
        var projectId = this.options[this.selectedIndex].id;
        fetchTasks(projectId, 'tasks2');
    });
}
dksjodjsjds()


// document.getElementById('projectAuto').addEventListener('change', function() {
//     var projectId = this.options[this.selectedIndex].id;
//     fetchTasks(projectId, 'tasksAuto');
// });


document.getElementById('project2').addEventListener('change', function() {
    var projectId = this.value;
    fetch(`../api/get_responsible_persons.php?project_id=${projectId}`)
        .then(response => response.json())
        .then(data => {
            var tasksSelect = document.getElementById('approved_by');
            tasksSelect.innerHTML = ''; // Clear existing options
            if (data.error) {
                console.error('Fehler:', data.error);
                alert('Fehler: ' + data.error);
            } else {
                data.forEach(task => {
                    var option = document.createElement('option');
                    option.value = task.user_id;
                    option.textContent = task.name;
                    tasksSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Fehler beim Abrufen der verantwortlichen Personen:', error));
});

function djsdjsj() {

    const buttons = document.querySelectorAll('.action-button');

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const timeEntryId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this entry?')) {
                fetch('../api/delete_time_entry.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({ 'time_entry_id': timeEntryId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Remove the row from the table
                            this.closest('tr').remove();
                        } else {
                            alert('Error deleting entry: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    });
    const buttonsAbs = document.querySelectorAll('.action-button-abs');

    buttonsAbs.forEach(button => {
        button.addEventListener('click', function() {
            const absencesID = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this entry?')) {
                fetch('../api/delete_absences_entry.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({ 'absences_id': absencesID })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Remove the row from the table
                            this.closest('tr').remove();
                        } else {
                            alert('Error deleting entry: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    });
}
djsdjsj();