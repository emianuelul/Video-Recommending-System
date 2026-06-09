const AuthSession = {

    isLoggedIn() {
        const token = localStorage.getItem("token");
        const createdAtRaw = localStorage.getItem("createdAt");
        const availableHours = Number(localStorage.getItem("availableHours") || 0);
        const createdAt = createdAtRaw ? new Date(createdAtRaw) : null;

        if (!token || !createdAt || isNaN(createdAt.getTime()) || availableHours <= 0) {
            return false;
        }

        const expiresAfter = availableHours * 60 * 60 * 1000;
        return Date.now() - createdAt.getTime() <= expiresAfter;
    },

    redirectIfLoggedIn() {
        if (this.isLoggedIn()) {
            window.location.replace("/page/search.html");
        }
    },

    saveSession(data) {
        localStorage.setItem("userId", data.userId);
        localStorage.setItem("token", data.token);
        localStorage.setItem("createdAt", data.createdAt);
        localStorage.setItem("availableHours", data.availableHours);
    },

    saveSessionAndRedirect(data) {
        this.saveSession(data);
        window.location.replace("/page/search.html");
    }
};

window.AuthSession = AuthSession;
