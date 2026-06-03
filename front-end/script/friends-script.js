const API_BASE = "http://localhost:8081/api/friends";

const loadFriendsForm = document.getElementById("load-friends-form");
const removeFriendForm = document.getElementById("remove-friend-form");
const userIdInput = localStorage.getItem("userId") || "";
const currentUserIdText = document.getElementById("current-user-id");
const friendIdInput = document.getElementById("friend-id");
const friendsList = document.getElementById("friends-list");
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

async function loadFriends() {
    const userId = userIdInput.trim();

    if (!userId) {
        setStatus("Missing user ID");
        return;
    }

    const url = `${API_BASE}/get_friends.php?user_id=${encodeURIComponent(userId)}`;
    const data = await fetchJson(url);

    if (data.status !== 200) {
        setStatus(data.message || "Could not load friends");
        return;
    }

    renderFriends(data.friends || []);
    setStatus(`Loaded ${(data.friends || []).length} friend(s)`);
}

function renderFriends(friends) {
    friendsList.innerHTML = "";

    if (friends.length === 0) {
        const item = document.createElement("li");
        item.className = "friend-list-item empty-state";
        item.textContent = "No friends found";
        friendsList.appendChild(item);
        return;
    }

    friends.forEach(friend => {
        const item = document.createElement("li");
        const info = document.createElement("div");
        const title = document.createElement("span");
        const meta = document.createElement("span");
        const actions = document.createElement("div");
        const removeButton = document.createElement("button");

        item.className = "friend-list-item";
        info.className = "friend-info";
        title.className = "friend-title";
        meta.className = "friend-meta";
        actions.className = "friend-list-actions";

        title.textContent = friend.friend_username;
        meta.textContent = `#${friend.friendship_id} ${friend.friend_id}`;

        removeButton.type = "button";
        removeButton.textContent = "Remove";
        removeButton.className = "btn danger";
        removeButton.addEventListener("click", () => removeFriendByFriendshipId(friend.friendship_id));

        info.appendChild(title);
        info.appendChild(meta);
        actions.appendChild(removeButton);
        item.appendChild(info);
        item.appendChild(actions);
        friendsList.appendChild(item);
    });
}

async function removeFriendByFriendshipId(friendshipId) {
    const data = await fetchJson(`${API_BASE}/remove_friend.php`, {
        method: "POST",
        body: JSON.stringify({ friendship_id: friendshipId })
    });

    setStatus(data.message || "Friend removed");

    if (data.status === 200) {
        loadFriends();
    }
}

async function removeFriendByUserIds() {
    const userId = userIdInput.trim();
    const friendId = friendIdInput.value.trim();

    if (!userId || !friendId) {
        setStatus("Missing friends IDs");
        return;
    }

    const data = await fetchJson(`${API_BASE}/remove_friend.php`, {
        method: "POST",
        body: JSON.stringify({
            user_id: userId,
            friend_id: friendId
        })
    });

    setStatus(data.message || "Friend removed");

    if (data.status === 200) {
        friendIdInput.value = "";
        loadFriends();
    }
}

loadFriendsForm.addEventListener("submit", event => {
    event.preventDefault();
    loadFriends();
});

removeFriendForm.addEventListener("submit", event => {
    event.preventDefault();
    removeFriendByUserIds();
});

if (userIdInput) {
    loadFriends();
}


const logoutButton = document.getElementById("logout");

logoutButton.addEventListener("click", event => {
    event.preventDefault();

    localStorage.removeItem("userId");
    localStorage.removeItem("token");
    localStorage.removeItem("createdAt");
    localStorage.removeItem("availableHours");
})
