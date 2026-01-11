const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');
const dash_a = document.getElementById('dashboard');
const switchMode = document.getElementById('switch-mode');
const fullscreen = document.getElementById('full-screen');

dash_a.classList.add('active');

function setCookie(cookieName, cookieValue, expirationDays) {
    localStorage.setItem(cookieName, cookieValue);
}

function getCookie(cookieName) {
    return localStorage.getItem(cookieName);
}

function deleteCookie(cookieName) {
    localStorage.removeItem(cookieName);
}

function getLanguage() {
    selectedLanguage = callerLanguageSelect.value;
    return selectedLanguage;
}

// Function to toggle full screen mode
function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

// Event listener for clicking on full screen image
if (fullscreen) {
    fullscreen.addEventListener("click", toggleFullScreen);
}

menuBar.addEventListener('click', function () {
    sidebar.classList.toggle('hide');
})


if (window.innerWidth < 768) {
    sidebar.classList.add('hide');
}


switchMode.addEventListener('change', function () {
    deleteCookie("mode");
    setCookie("mode", this.checked, 7);
    if (this.checked) {
        document.body.classList.remove('dark');
    } else {
        document.body.classList.add('dark');
    }
});

window.addEventListener('load', function () {
    var modeCookie = getCookie("mode");
    if (modeCookie != null) {
        if (modeCookie == 'true') {
            document.body.classList.remove('dark');
            switchMode.checked = true;
        } else {
            document.body.classList.add('dark');
            switchMode.checked = false;
        }
    }

    // Load User Specific Data
    const currentUser = localStorage.getItem('currentUser') || 'Guest';
    const profileName = document.getElementById('profile-name');
    if (profileName) profileName.innerText = currentUser;

    const shops = JSON.parse(localStorage.getItem('dallol_shops')) || [];
    const myShop = shops.find(s => s.name.toLowerCase() === currentUser.toLowerCase());

    if (myShop) {
        const balanceEl = document.getElementById('available-balance');
        const earningEl = document.getElementById('today-earning');
        if (balanceEl) balanceEl.innerText = 'ETB ' + myShop.balance;
        if (earningEl) earningEl.innerText = 'ETB ' + (myShop.totalEarning || '0.00');
    }
});
