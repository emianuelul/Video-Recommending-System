const API_BASE = "http://localhost:8081/api/friends";

const loadRequestsForm = document.getElementById("load-requests-form");
const userIdInput = localStorage.getItem("userId") || "";
const currentUserIdText = document.getElementById("current-user-id");
const requestTypeSelect = document.getElementById("request-type");
const requestsList = document.getElementById("requests-list");
const statusText = document.getElementById("status");

currentUserIdText.textContent = userIdInput || "Not logged in";

function setStatus(message) {
    statusText.textContent = message;
}

async function fetchJson(url, options = {}) {
    const response = await fetch(url, {
        credentials: "include",
        ...options,
        headers: {
            "Content-Type": "application/json",
            ...(options.headers || {})
        }
    });

    return response.json();
}

async function loadRequests() {
    const userId = userIdInput.trim();
    const type = requestTypeSelect.value;

    if (!userId) {
        setStatus("Missing user ID");
        return;
    }

    const url = `${API_BASE}/get_friend_requests.php?user_id=${encodeURIComponent(userId)}&type=${encodeURIComponent(type)}`;
    const data = await fetchJson(url);

    if (data.status !== 200) {
        setStatus(data.message || "Could not load friend requests");
        return;
    }

    renderRequests(data.requests || []);
    setStatus(`Loaded ${(data.requests || []).length} request(s)`);
}

function renderRequests(requests) {
    requestsList.innerHTML = "";

    if (requests.length === 0) {
        const item = document.createElement("li");
        item.className = "friend-list-item empty-state";
        item.textContent = "No friend requests found";
        requestsList.appendChild(item);
        return;
    }

    requests.forEach(request => {
        const item = document.createElement("li");
        const info = document.createElement("div");
        const title = document.createElement("span");
        const meta = document.createElement("span");

        item.className = "friend-list-item";
        info.className = "friend-info";
        title.className = "friend-title";
        meta.className = "friend-meta";

        title.textContent = request.type === "incoming"
            ? `${request.requester_username} sent a request`
            : `Request sent to ${request.receiver_username}`;
        meta.textContent = `Created ${request.created_at}`;

        info.appendChild(title);
        info.appendChild(meta);
        item.appendChild(info);

        if (request.type === "incoming") {
            const actions = document.createElement("div");
            const acceptButton = document.createElement("button");
            const refuseButton = document.createElement("button");

            actions.className = "friend-list-actions";

            acceptButton.type = "button";
            acceptButton.textContent = "Accept";
            acceptButton.className = "btn success";
            acceptButton.addEventListener("click", () => updateRequest("accept_friend.php", request));

            refuseButton.type = "button";
            refuseButton.textContent = "Refuse";
            refuseButton.className = "btn danger";
            refuseButton.addEventListener("click", () => updateRequest("refuse_friend.php", request));

            actions.appendChild(acceptButton);
            actions.appendChild(refuseButton);
            item.appendChild(actions);
        } else {
            const pendingText = document.createElement("span");
            pendingText.className = "status-pill";
            pendingText.textContent = "Pending";
            item.appendChild(pendingText);
        }

        requestsList.appendChild(item);
    });
}

async function updateRequest(endpoint, request) {
    const currentUserId = localStorage.getItem("userId");

    if (!currentUserId) {
        setStatus("Missing logged in user ID");
        return;
    }

    const friendUsername = request.requester_id === currentUserId
        ? request.receiver_username
        : request.requester_username;

    const data = await fetchJson(`${API_BASE}/${endpoint}`, {
        method: "POST",
        body: JSON.stringify({
            friend1_id: currentUserId,
            friend_username: friendUsername
        })
    });

    setStatus(data.message || "Request updated");

    if (data.status === 200) {
        loadRequests();
    }
}

loadRequestsForm.addEventListener("submit", event => {
    event.preventDefault();
    loadRequests();
});

if (userIdInput) {
    loadRequests();
}

new LogoutButton("logout");
