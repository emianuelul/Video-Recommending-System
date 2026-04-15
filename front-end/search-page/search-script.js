const searchBar = document.querySelector('#search-bar');
const videoDurationSelect = document.querySelector('#video-duration');
const afterTime = document.querySelector('#after-time');
const beforeTime = document.querySelector('#before-time');
const languageSelect = document.querySelector('#language-select');
const orderSelect = document.querySelector('#order-select');
const searchBtn = document.querySelector('.search-btn');
const results = document.querySelector('.result');

function convertToRfc3339(dateString) {
    let output = "";

    if (dateString !== "") {
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0'); // e 0 indexed lmao ?????
        const day = String(date.getDate()).padStart(2, '0');

        output = `${year}-${month}-${day}T00:00:00Z`;
    }

    return output;
}

searchBtn.addEventListener('click', () => {
    let queryParams = "?";
    const q = searchBar.value;
    const videoDuration = videoDurationSelect.value;
    const publishedAfter = convertToRfc3339(afterTime.value);
    const publishedBefore = convertToRfc3339(beforeTime.value);
    const relevanceLanguage = languageSelect.value;
    const order = orderSelect.value;

    if (q !== "") {
        queryParams += `q=${q}`;
        if (videoDuration !== "") {
            queryParams += `&videoDuration=${videoDuration}`;
        }
        if (publishedAfter !== "") {
            queryParams += `&publishedAfter=${publishedAfter}`;
        }
        if (publishedBefore !== "") {
            queryParams += `&publishedBefore=${publishedBefore}`;
        }
        if (relevanceLanguage !== "") {
            queryParams += `&relevanceLanguage=${relevanceLanguage}`;
        }
        if (order !== "") {
            queryParams += `&order=${order}`;
        }

        fetch(`http://localhost:8080/api/search.php${queryParams}`)
            .then(res => res.json())
            .then(data => console.log(data));
    }

})