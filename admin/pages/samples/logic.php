<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
let wort, second_name, uebersetzung, wortArt, first_name,imageSource1, wort_id;
let errateneWoerter = 0;
let falscheVersuche = 0;
let zeicher=1;
var isModalOpen = false;
var loginModal = document.getElementById('loginPopup');
var registerModal = document.getElementById('registerPopup');
var loginBtn = document.getElementById('loginBtn');
var registerBtn = document.getElementById('registerBtn');
var spanClose = document.getElementsByClassName('close');
var user = '<?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>';

function updateUserName(){
    if(user !== undefined && user !== null && user !== ""){
        document.getElementById('nicht_angemeldet').textContent="Herzlich willkommen " + user;
    }
}

loginBtn.onclick = function() {
    loginModal.style.display = 'block';
    isModalOpen = true; // Setze isModalOpen auf true, wenn das Modal geöffnet wird
}


// registerBtn.onclick = function() {
//     registerModal.style.display = 'block';
//     isModalOpen = true; // Setze isModalOpen auf true, wenn das Modal geöffnet wird
// }

for (var i = 0; i < spanClose.length; i++) {
    spanClose[i].onclick = function() {
        loginModal.style.display = 'none';
        registerModal.style.display = 'none';
        isModalOpen = false; // Setze isModalOpen auf false, wenn das Modal geschlossen wird
    }
}

window.onclick = function(event) {
    if (event.target == loginModal || event.target == registerModal) {
        loginModal.style.display = 'none';
        registerModal.style.display = 'none';
        isModalOpen = false; // Setze isModalOpen auf false, wenn das Modal geschlossen wird
    }
}

// if (window.location.pathname === '/test/globetrotter1.php') {
//     console.log("Das Skript befindet sich auf der Seite Globetrotter.");
// }


updateUserName();

// imageSource1 = `dateien/${zeicher}.svg`;
// document.getElementById('falschGalgen').src = imageSource1;

// function ladeNeuesWort() {
//     const xhr = new XMLHttpRequest();
//     xhr.open("GET", "dateien/dbquery.php", true);
//     xhr.onload = function () {
//         if (xhr.status === 200) {
//             try {
//                 const data = JSON.parse(xhr.responseText);
//                 if (data && data.english && data.definition && data.german && data.wortart) {
//                     wort = data.english.toUpperCase();
//                     definition = data.definition;
//                     uebersetzung = data.german;
//                     wortArt = data.wortart;
//                     wort_id = data.id;
//                     rateWort = wort.replace(/[^\s()'/-]/g, '_');
//                     //rateWort = wort.replace(/\S/g, '_');
//                     aktualisiereAnzeige();
//                 } else {
//                     console.error("Fehler: Unvollständige Daten empfangen.");
//                 }
//             } catch (e) {
//                 console.error("Fehler beim Parsen der JSON-Antwort: ", e);
//             }
//         } else {
//             console.error("Fehler beim Laden neuer Daten: Status ", xhr.status);
//         }
//     };
//     xhr.onerror = function () {
//         console.error("Netzwerkfehler oder kein Zugriff auf den Server.");
//     };
//     xhr.send();
// }


// function aktualisiereAnzeige() {
//     document.getElementById('first_name').textContent = first_name;
//     document.getElementById('second_name').textContent = second_name;
    // document.getElementById('_deuWort').textContent = uebersetzung;
    // document.getElementById('_wortArt').textContent = wortArt;
// }
// aktualisiereAnzeige();
// function spielInitialisieren() {
//     ladeNeuesWort();
// }

// //
// function buchstabeRaten(buchstabe) {
//     let erraten = false;

//     for (let i = 0; i < wort.length; i++) {
//         if (wort[i] === buchstabe) {
//         if(buchstabe===' '){
//         rateWort = rateWort.substring(0, i * 1) + ' ' + rateWort.substring(i * 1 + 1);
//         }
//             rateWort = rateWort.substring(0, i * 1) + buchstabe + rateWort.substring(i * 1 + 1);
//             erraten = true;
//         }
//     }

//     if (!erraten) {
//         falscheVersuche++;
//         zeicher++;
//         document.getElementById('falschCounter').textContent = falscheVersuche;
//         imageSource1 = `dateien/${zeicher}.svg`;
//         document.getElementById('falschGalgen').src = imageSource1;
//     }

//     document.getElementById('wortAnzeige').textContent = rateWort;
// //

//     if (!rateWort.includes('_')) {
//     // alert('Gewonnen!');
//     Swal.fire({
//   title: 'Gewonnen!',
//   text: 'Sie haben das Wort erraten.',
//   icon: 'success',
//   confirmButtonText: 'Wieter spielen'
// });
//     errateneWoerter++;
//     document.getElementById('woerterCounter').textContent = errateneWoerter;
//     update_statistic();
//     if (falscheVersuche>0){
//         falscheVersuche--;
//         document.getElementById('falschCounter').textContent = falscheVersuche;
//         zeicher--;
//         imageSource1 = `dateien/${zeicher}.svg`;
//         document.getElementById('falschGalgen').src = imageSource1;
//         }
//     spielInitialisieren();
//     }
//     if (falscheVersuche >= 7) {
//         update_statistic();
//         setTimeout(function() {
//     Swal.fire({
//         title: 'Verloren!',
//         text: 'Sie haben leider verloren. Das Wort war: ' + wort + '. Ihr Spielstand wird zurückgesetzt.',
//         icon: 'error',
//         confirmButtonText: 'Nochmal spielen'
//     }).then((result) => {
//         if (result.isConfirmed) {
//             setTimeout(function() {
//                 location.reload();
//             }, 100);
//         }
//     });
// }, 100); // 500ms Verzögerung, bevor der SweetAlert2 Dialog angezeigt wird
// }
// }




// function generiereBuchstabenButtons() {
//     let buttonsHTML = '';
//     'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('').forEach(buchstabe => {
//         buttonsHTML += `<button onClick="buchstabeRaten('${buchstabe}')">${buchstabe}</button>`;
//     }
//     );
//     document.getElementById('buchstabenButtons').innerHTML = buttonsHTML;
// }

// function handleKeyPress(event) {
//     if (isModalOpen) {

//         return;
//     }
//     const key = event.key;
//     const keyCode = event.keyCode; 

//     if ((keyCode >= 65 && keyCode <= 90) || keyCode == 32) {        
//         buchstabeRaten(key.toUpperCase());
//     }
// }
// document.addEventListener('keydown', handleKeyPress);


// function update_statistic(){

//     var statisticData ={
//     errateneWoerter : errateneWoerter,
//     username: user,
//     falschCounter: falscheVersuche,
//     wort_id: wort_id
//         };
//     console.log (statisticData);

//     var xhr1 = new XMLHttpRequest();
//     xhr1.open('POST', 'dateien/statisticks.php', true);
//     xhr1.setRequestHeader('Content-Type', 'application/json');
//     xhr1.onload = function() {
//         if (xhr1.status >= 200 && xhr1.status < 300) {
//             // Erfolgreiche Anfrage
//             console.log("Datenbank erfolgreich aktualisiert: " + xhr1.responseText);
//         } else {
//             // Fehler bei der Anfrage
//             console.error("Fehler beim Aktualisieren der Datenbank: " + xhr1.statusText);
//         }
//     };
//     // Fehlerbehandlung für die Anfrage
//     xhr1.onerror = function() {
//         console.error("Fehler beim Senden der Anfrage.");
//     };
//     // Daten senden
//     xhr1.send(JSON.stringify(statisticData));
// }



// generiereBuchstabenButtons();
// spielInitialisieren();
// handleKeyPress();

</script>