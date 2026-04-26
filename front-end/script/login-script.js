const usernameInput = document.querySelector('#username');
const passwordInput = document.querySelector('#password');
const submitBtn = document.querySelector('#login-submit');

const url = "http://localhost:8081/api/login.php";

submitBtn.addEventListener('click', () => {
    const username = usernameInput.value.trim();
    const password = passwordInput.value.trim();

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            "username": username,
            "password": password
        })
    }).then(async r => {
        const resp = await r.json();
        switch (resp.status) {
            case 200: {
                alert(resp.message);

                localStorage.setItem("userId", resp.userId);
                localStorage.setItem("token", resp.token);
                localStorage.setItem("createdAt", resp.createdAt);
                localStorage.setItem("availableHours", resp.availableHours);

                // console.log(localStorage);
                // maybe go to home page or smth

                break;
            }
            case 409: {
                alert(resp.message);
                break;
            }
            default: {
                alert("Unexpected error: " + resp.message);
                break;
            }
        }
    })
})