const ADMIN_USERS_URL = "http://localhost:8081/api/admin/users.php";
const ADMIN_LOGIN_PAGE = "/page/admin/login.html";

const loadUsersButton = document.getElementById("load-users");
const logoutButton = document.getElementById("admin-logout");
const usersList = document.getElementById("admin-users-list");
const statusText = document.getElementById("admin-status");

function getAdminToken() {
    return localStorage.getItem("adminToken") || "";
}

function requireAdmin() {
    if (!getAdminToken()) {
        window.location.replace(ADMIN_LOGIN_PAGE);
    }
}

function setStatus(message) {
    statusText.textContent = message;
}

function clearAdminSession() {
    localStorage.removeItem("adminToken");
    localStorage.removeItem("adminId");
    localStorage.removeItem("adminCreatedAt");
    localStorage.removeItem("adminAvailableHours");
}

async function adminFetch(url, options = {}) {
    const response = await fetch(url, {
        credentials: "include",
        ...options,
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${getAdminToken()}`,
            ...(options.headers || {})
        }
    });

    return response.json();
}

async function loadUsers() {
    setStatus("Loading users...");

    const data = await adminFetch(ADMIN_USERS_URL);

    if (data.status === 401) {
        clearAdminSession();
        window.location.replace(ADMIN_LOGIN_PAGE);
        return;
    }

    if (data.status !== 200) {
        setStatus(data.message || "Could not load users");
        return;
    }

    renderUsers(data.users || []);
    setStatus(`Loaded ${(data.users || []).length} user(s)`);
}

function renderUsers(users) {
    usersList.innerHTML = "";

    if (users.length === 0) {
        const item = document.createElement("li");
        item.className = "admin-user-card empty";
        item.textContent = "No users found";
        usersList.appendChild(item);
        return;
    }

    users.forEach(user => {
        const item = document.createElement("li");
        const info = document.createElement("div");
        const title = document.createElement("h3");
        const meta = document.createElement("p");
        const actions = document.createElement("div");
        const passwordInput = document.createElement("input");
        const changeButton = document.createElement("button");
        const deleteButton = document.createElement("button");

        item.className = "admin-user-card";
        info.className = "admin-user-info";
        actions.className = "admin-user-actions";

        title.textContent = user.username;
        meta.textContent = `${user.id} | created ${user.created_at}`;

        passwordInput.type = "password";
        passwordInput.placeholder = "New password";

        changeButton.type = "button";
        changeButton.className = "admin-btn primary";
        changeButton.textContent = "Change password";
        changeButton.addEventListener("click", () => changePassword(user.id, passwordInput.value));

        deleteButton.type = "button";
        deleteButton.className = "admin-btn danger";
        deleteButton.textContent = "Delete";
            deleteButton.addEventListener("click", () => deleteUser(user.id, user.username, item));

        info.append(title, meta);
        actions.append(passwordInput, changeButton, deleteButton);
        item.append(info, actions);
        usersList.appendChild(item);
    });
}

async function changePassword(userId, newPassword) {
    if (!newPassword.trim()) {
        setStatus("Type a new password first");
        return;
    }

    const data = await adminFetch(ADMIN_USERS_URL, {
        method: "POST",
        body: JSON.stringify({
            action: "change_password",
            user_id: userId,
            new_password: newPassword
        })
    });

    setStatus(data.message || "Password action finished");
}

async function deleteUser(userId, username, userItem) {
    if (!confirm(`Delete user ${username}?`)) {
        return;
    }

    const data = await adminFetch(ADMIN_USERS_URL, {
        method: "POST",
        body: JSON.stringify({
            action: "delete_user",
            user_id: userId
        })
    });

    setStatus(data.message || "Delete action finished");

    if (data.status === 200) {
        userItem.remove();
        loadUsers();
    }
}

logoutButton.addEventListener("click", () => {
    clearAdminSession();
    window.location.replace(ADMIN_LOGIN_PAGE);
});

loadUsersButton.addEventListener("click", loadUsers);

requireAdmin();
loadUsers();
