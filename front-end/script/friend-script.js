const submitBtn = document.getElementById("submit");
const friend2Input = document.getElementById("friend2");
const currentUserIdText = document.getElementById("current-user-id");
const statusText = document.getElementById("status");

currentUserIdText.textContent = localStorage.getItem("userId") || "Not logged in";

function setStatus(message) {
    statusText.textContent = message;
}

submitBtn.addEventListener('click', (e) => {
    e.preventDefault();
    const id2 = friend2Input.value.trim();
    const currentUserId = localStorage.getItem("userId");

    if (!currentUserId) {
        setStatus("Missing logged in user ID");
        return;
    }

    const url = `http://localhost:8081/api/friends/add_friend.php`;

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            friend1_id: currentUserId.trim(),
            friend2_id: id2
        }),
        credentials: "include",
    })
        .then(async r => {
            const resp = await r.json();

            switch (resp.status) {
                case 200:
                    friend2Input.value = "";
                    setStatus(resp.message);
                    break;
                case 409:
                    setStatus(resp.message);
                    break;
                default:
                    setStatus("Unexpected error: " + resp.message);
                    break;
            }
        })
        .catch(err => {
            console.error(err);
            setStatus("Request failed");
        });
})
