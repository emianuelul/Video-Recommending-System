const ADMIN_LOGIN_URL = "http://localhost:8081/api/admin/login.php";
const ADMIN_USERS_PAGE = "/page/admin/users.html";

const form = document.getElementById("admin-login-form");
const usernameInput = document.getElementById("admin-username");
const passwordInput = document.getElementById("admin-password");
const statusText = document.getElementById("admin-status");

function isAdminLoggedIn() {
    return localStorage.getItem("adminToken") !== null &&
        localStorage.getItem("adminCreatedAt") !== null;
}

if (isAdminLoggedIn()) {
    window.location.replace(ADMIN_USERS_PAGE);
}

function setStatus(message) {
    statusText.textContent = message;
}

form.addEventListener("submit", event => {
    event.preventDefault();

    fetch(ADMIN_LOGIN_URL, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            username: usernameInput.value.trim(),
            password: passwordInput.value.trim()
        }),
        credentials: "include"
    })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 200) {
                setStatus(data.message || "Admin login failed");
                return;
            }

            localStorage.setItem("adminToken", data.adminToken);
            localStorage.setItem("adminId", data.adminId);
            localStorage.setItem("adminCreatedAt", data.createdAt);
            localStorage.setItem("adminAvailableHours", data.availableHours);

            window.location.replace(ADMIN_USERS_PAGE);
        })
        .catch(err => {
            console.error(err);
            setStatus("Request failed");
        });
});
