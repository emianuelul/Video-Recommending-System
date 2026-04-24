const usernameInput = document.querySelector('#username');
const passwordInput = document.querySelector('#password');
const confirmPasswordInput = document.querySelector('#confirm-password');
const submitBtn = document.querySelector('#register-submit');

const url = "http://localhost:8081/api/register.php";

submitBtn.addEventListener('click', (e) => {
    e.preventDefault();

    const checkedCategories = [...document.querySelectorAll('.categories-list input:checked')]
        .map(btn => btn.value);

    const checkedLanguages = [...document.querySelectorAll('.languages-list input:checked')]
        .map(btn => btn.value);

    const checkedDurations = [...document.querySelectorAll('.duration-list input:checked')]
        .map(btn => btn.value);

    const selectedCountry = document.querySelector('#country-select').value;


    const username = usernameInput.value.trim();
    const password = passwordInput.value.trim();
    const confirmPassword = confirmPasswordInput.value.trim();
    
    if(confirmPassword !== password){
        confirmPasswordInput.setCustomValidity("Passwords don't match");
        confirmPasswordInput.reportValidity();
        return;
    }
    
    confirmPasswordInput.setCustomValidity("");
    
    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            "username": username,
            "password": password,
            "categories": checkedCategories,
            "languages": checkedLanguages,
            "durations": checkedDurations,
            "country": selectedCountry
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