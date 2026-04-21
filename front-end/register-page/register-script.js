const usernameInput = document.querySelector('#username');
const passwordInput = document.querySelector('#password');
const submitBtn = document.querySelector('#register-submit');

const url = "http://localhost:8081/api/register.php";

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