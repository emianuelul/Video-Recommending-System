class LogoutButton {
    constructor(buttonId = "logout") {
        this.button = document.getElementById(buttonId);

        if (!this.button) {
            return;
        }

        this.button.addEventListener("click", event => {
            event.preventDefault();
            this.logout();
        });
    }

    logout() {
        localStorage.removeItem("userId");
        localStorage.removeItem("token");
        localStorage.removeItem("createdAt");
        localStorage.removeItem("availableHours");
        window.location.replace("/page/login.html");
    }
}

window.LogoutButton = LogoutButton;
