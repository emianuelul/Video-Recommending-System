const submitBtn = document.getElementById("submit");
const friend1Input = document.getElementById("friend1");
const friend2Input = document.getElementById("friend2");

submitBtn.addEventListener('click', (e) => {
    e.preventDefault();
    // const id1 = friend1Input.value.trim();
    const id2 = friend2Input.value.trim();

    const url = `http://localhost:8081/api/friends/add_friend.php`;

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            friend1_id: localStorage.getItem("userId"),
            friend2_id: id2
        }),
        credentials: "include",
    })
        .then(async r => {
            const resp = await r.json();

            switch (resp.status) {
                case 200:
                    console.log(resp.message);
                    break;
                case 409:
                    alert(resp.message);
                    break;
                default:
                    alert("Unexpected error: " + resp.message);
                    break;
            }
        })
        .catch(err => {
            console.error(err);
            alert("Request failed");
        });
})
