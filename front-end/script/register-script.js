const usernameInput = document.querySelector('#username');
const passwordInput = document.querySelector('#password');
const confirmPasswordInput = document.querySelector('#confirm-password');
const submitBtn = document.querySelector('#register-submit');

const url = "http://localhost:8081/back-end/api/register.php";

submitBtn.addEventListener('click', (e) => {
    e.preventDefault();

    const checkedCategories = [...document.querySelectorAll('.categories-list input:checked')]
        .map(btn => parseInt(btn.value));

    const checkedLanguages = [...document.querySelectorAll('.languages-list input:checked')]
        .map(btn => btn.value);

    const checkedDurations = [...document.querySelectorAll('.duration-list input:checked')]
        .map(btn => btn.value);

    const selectedCountry = document.querySelector('#country-select').value;


    const username = usernameInput.value.trim();
    const password = passwordInput.value.trim();
    const confirmPassword = confirmPasswordInput.value.trim();

    if (confirmPassword !== password) {
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

const dropdown = document.getElementById("selectDropdown");
const button = dropdown.querySelector(".dropdown-btn");
const checkboxes = dropdown.querySelectorAll("input[type='checkbox']");

button.addEventListener("click", () => {
    dropdown.classList.toggle("open");
});

checkboxes.forEach(checkbox => {
    checkbox.addEventListener("change", () => {
        const selected = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.parentNode.textContent);

        button.textContent = selected.length
            ? selected.join(", ")
            : "Select options";
    });
});

document.addEventListener("click", (event) => {
    if (!dropdown.contains(event.target)) {
        dropdown.classList.remove("open");
    }
});


//pentru modificarea marimii scrollbar-ului atunci cand in buton apar mai multe cuvinte sau dispar altele
let firstAppear = true;
let customScrollbar = document.querySelector('.custom-scrollbar-select');
const observer1 = new MutationObserver(() => {
    const totalScrollable = button.scrollWidth - button.clientWidth;

    if (totalScrollable > 0) {
        if (totalScrollable > button.clientWidth) {
            console.log(totalScrollable, button.scrollWidth, button.clientWidth);
        }
        customScrollbar.style.width = `calc(100% - ${totalScrollable}px - 22px)`;
        customScrollbar.style.maxWidth = `calc(100% - ${totalScrollable}px - 22px)`;

        //prima oara cand apare scrollbar-ul, caci e nevoie, sa fie vizibil 500ms ca sa stie user-ul ca poate da scroll
        if (firstAppear) {
            firstAppear = false;
            customScrollbar.style.opacity = 1;

            setTimeout(() => {
                customScrollbar.style.opacity = 0;
            }, 500);
        }
    } else if (totalScrollable === 0 && customScrollbar.style.width !== 0) {
        customScrollbar.style.width = `0px`;
        customScrollbar.style.transform = `translateX(0px)`;
        firstAppear = true;
    }
});

observer1.observe(button, {
    childList: true,
    subtree: true,
    characterData: true
});


//pentru actualizarea pozitiei scrollbar-ului cand dai scroll si cand scoti ceva din select si se face si marimea scrollbarului mai mica
function update() {
    const totalScrollable = button.scrollWidth - button.clientWidth;

    customScrollbar.style.transform = `translateX(${totalScrollable > 220 ? button.scrollLeft / (totalScrollable / 220) : button.scrollLeft}px)`;
}

button.addEventListener("scroll", update);
window.addEventListener("load", update);

const observer2 = new MutationObserver(update);

observer2.observe(button, {
    childList: true,
    subtree: true,
    characterData: true
})


//pentru ca sa fie vizibil scrollbar-ul numai cand dai scroll
let isScrolling = false;
let scrollTimeout;
button.addEventListener("scroll", () => {
    if (!isScrolling) {
        isScrolling = true;
        customScrollbar.style.opacity = 1
    }

    clearTimeout(scrollTimeout);

    scrollTimeout = setTimeout(() => {
        isScrolling = false;
        customScrollbar.style.opacity = 0;
    }, 500);
});
