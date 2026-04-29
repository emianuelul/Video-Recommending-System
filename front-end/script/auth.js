const body = document.querySelector('body');

function redirect() {
    if (window.location.pathname !== "/page/login.html") {
        localStorage.clear();
        window.location.replace("/page/login.html");
    }
}

function isTokenSet() {
    return localStorage.getItem("token") !== null &&
           localStorage.getItem("createdAt") !== null;
}

document.body.onload = () => {
    if (!isTokenSet()) {
        redirect();
        return;
    }

    const HOUR_COUNT = localStorage.availableHours
        ? Number(localStorage.availableHours)
        : 0;

    const HOUR_IN_MS = 1000 * 60 * 60;
    const EXPIRES_AFTER = HOUR_IN_MS * HOUR_COUNT;

    const createdAtRaw = localStorage.getItem("createdAt");
    const createdAt = createdAtRaw ? new Date(createdAtRaw) : null;

    if (!createdAt || isNaN(createdAt.getTime())) {
        redirect();
        return;
    }

    const timeSinceLastToken = Date.now() - createdAt.getTime();

    if (timeSinceLastToken > EXPIRES_AFTER) {
        redirect();
    }
};
