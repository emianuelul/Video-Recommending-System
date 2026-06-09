const ACCOUNT_URL = "http://localhost:8081/api/user/account.php";

const usernameText = document.getElementById("account-username");
const userIdText = document.getElementById("account-user-id");
const createdAtText = document.getElementById("account-created-at");
const countryText = document.getElementById("account-country");
const languagesText = document.getElementById("account-languages");
const durationsText = document.getElementById("account-durations");
const categoriesText = document.getElementById("account-categories");
const statusText = document.getElementById("account-status");
const changePasswordForm = document.getElementById("change-password-form");
const deleteAccountForm = document.getElementById("delete-account-form");

function getToken() {
    return localStorage.getItem("token") || "";
}

function clearSession() {
    localStorage.removeItem("userId");
    localStorage.removeItem("token");
    localStorage.removeItem("createdAt");
    localStorage.removeItem("availableHours");
}

function setStatus(message) {
    statusText.textContent = message;
}

function parseJsonList(value) {
    if (!value) {
        return [];
    }

    try {
        const parsed = JSON.parse(value);
        return Array.isArray(parsed) ? parsed : [];
    } catch (err) {
        return [];
    }
}

async function accountFetch(options = {}) {
    const response = await fetch(ACCOUNT_URL, {
        credentials: "include",
        ...options,
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${getToken()}`,
            ...(options.headers || {})
        }
    });

    const data = await response.json();

    if (response.status === 401) {
        clearSession();
        window.location.replace("/page/login.html");
    }

    return data;
}

async function loadAccount() {
    const data = await accountFetch();

    if (data.status !== 200) {
        setStatus(data.message || "Could not load account");
        return;
    }

    const preferences = data.preferences || {};
    const languages = parseJsonList(preferences.languages);
    const durations = parseJsonList(preferences.duration);
    const categories = data.categories || [];

    usernameText.textContent = data.user.username;
    userIdText.textContent = data.user.id;
    createdAtText.textContent = data.user.created_at;
    countryText.textContent = preferences.country || "-";
    languagesText.textContent = languages.length ? languages.join(", ") : "-";
    durationsText.textContent = durations.length ? durations.join(", ") : "-";
    categoriesText.textContent = categories.length
        ? categories.map(category => category.category_id).join(", ")
        : "-";
}

async function changePassword(event) {
    event.preventDefault();

    const currentPasswordInput = document.getElementById("current-password");
    const newPasswordInput = document.getElementById("new-password");
    const currentPassword = currentPasswordInput.value.trim();
    const newPassword = newPasswordInput.value.trim();

    if (!currentPassword || !newPassword) {
        setStatus("Complete both password fields");
        return;
    }

    const data = await accountFetch({
        method: "POST",
        body: JSON.stringify({
            action: "change_password",
            current_password: currentPassword,
            new_password: newPassword
        })
    });

    setStatus(data.message || "Password action finished");

    if (data.status === 200) {
        changePasswordForm.reset();
    }
}

async function deleteAccount(event) {
    event.preventDefault();

    if (!confirm("Delete your account?")) {
        return;
    }

    const passwordInput = document.getElementById("delete-password");
    const currentPassword = passwordInput.value.trim();

    if (!currentPassword) {
        setStatus("Type your current password first");
        return;
    }

    const data = await accountFetch({
        method: "POST",
        body: JSON.stringify({
            action: "delete_account",
            current_password: currentPassword
        })
    });

    setStatus(data.message || "Delete action finished");

    if (data.status === 200) {
        clearSession();
        window.location.replace("/page/login.html");
    }
}

changePasswordForm.addEventListener("submit", changePassword);
deleteAccountForm.addEventListener("submit", deleteAccount);

new LogoutButton("logout");
loadAccount();
