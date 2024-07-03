// Funktion zum Laden der Aufgaben
function fetchTasks(projectId, tasksElementId) {
    fetch(`get_tasks.php?projectId=${projectId}`)
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
document.getElementById('project1').addEventListener('change', function() {
    var projectId = this.options[this.selectedIndex].id;
    fetchTasks(projectId, 'tasks1');
});

document.getElementById('project2').addEventListener('change', function() {
    var projectId = this.options[this.selectedIndex].id;
    fetchTasks(projectId, 'tasks2');
});


    document.getElementById('project2').addEventListener('change', function() {
var projectId = this.value;
fetch(`get_responsible_persons.php?project_id=${projectId}`)
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