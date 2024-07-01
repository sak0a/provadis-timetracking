function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        let date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/"; // Set path to '/'
}

function getCookie(name) {
    let nameEQ = name + "=";
    let ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

document.addEventListener("DOMContentLoaded", function() {
    /**
     * Dark mode toggle functionality . START
     */
    document.getElementById("theme-toggle").addEventListener("click", function() {
        document.documentElement.classList.toggle("dark-mode");
        // Save dark mode preference in a cookie
        if (document.documentElement.classList.contains("dark-mode")) {
            setCookie("darkMode", "enabled", 365);
            document.getElementById('theme-toggle').classList.add('theme-toggle--toggled');
        } else {
            document.getElementById('theme-toggle').classList.remove('theme-toggle--toggled');
            setCookie("darkMode", "disabled", 365);
        }
    });

    // Check for dark mode cookie
    if (getCookie("darkMode") === "enabled") {
        document.documentElement.classList.add("dark-mode");
    }
    /**
     * Dark mode toggle functionality . END
     */


});