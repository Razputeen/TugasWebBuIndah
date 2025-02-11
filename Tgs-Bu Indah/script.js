document.addEventListener("DOMContentLoaded", function() {
    const loadingScreen = document.getElementById('loading-screen');
    const loginScreen = document.getElementById('login-screen');
    const registrationScreen = document.getElementById('registration-screen');

    // Set a timeout to hide the loading screen and show the login screen
    setTimeout(() => {
        loadingScreen.style.display = 'none';
        loginScreen.style.display = 'block';
    }, 2000); // Adjust this duration as needed (2000ms = 2 seconds)

    // Functions to toggle between login and registration screens
    function showRegistration() {
        loginScreen.style.display = 'none';
        registrationScreen.style.display = 'block';
    }

    function showLogin() {
        registrationScreen.style.display = 'none';
        loginScreen.style.display = 'block';
    }

    // Expose functions to the global scope for button click events
    window.showRegistration = showRegistration;
    window.showLogin = showLogin;
});

function navigateToMain() {
    window.location.href = "main.php";
}


