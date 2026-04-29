import DOMStuff from "./DOMStuff.js";

const inputForm = document.querySelector('.login-fields-container')
const url = "http://localhost:8081/api/login.php";

const loginInputPass = document.querySelector('.login-input.pass-input');
const passwordNodes = DOMStuff.createPasswordInput();
loginInputPass.append(passwordNodes[0], passwordNodes[1], passwordNodes[2]);

const userInputPass = document.querySelector('.login-input.user-input');
const userNodes = DOMStuff.createUsernameInput();
userInputPass.append(userNodes[0], userNodes[1], userNodes[2]);

const imgLock = document.querySelector(".passwordVis");
const inPass = document.querySelector("#password");
imgLock.addEventListener("click", (e) => {
    e.preventDefault();
    if (imgLock.src.includes("unlock-icon.svg")) {
        imgLock.src = "../libs/images/auth/lock-icon.svg";
        inPass.type = "password";
    } else {
        imgLock.src = "../libs/images/auth/unlock-icon.svg";
        inPass.type = "text";
    }
})


inputForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const usernameInput = document.querySelector('#username');
    const passwordInput = document.querySelector('#password');

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
