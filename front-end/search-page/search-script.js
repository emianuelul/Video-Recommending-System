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

        let resultsDiv = document.getElementById('results');
        let data = fetch(`http://localhost:8000/api/search.php${queryParams}`, {method: 'GET', credentials: 'include'})
            .then(res => res.json())
            .then(data => console.log(data));

        resultsDiv.innerHTML = "";

        if (!data.items || data.items.length === 0) {
            resultsDiv.innerHTML = "<p>No results found.</p>";
            return;
        }

        data.items.forEach(video => {
            const videoId = video.id.videoId;
            const title = video.snippet.title;
            const description = video.snippet.description;
            const thumbnail = video.snippet.thumbnails.medium.url;

            resultsDiv.innerHTML += `
                    <div class="video">
                        <h3>${title}</h3>
                        <img src="${thumbnail}" alt="${title}">
                        <p>${description}</p>
                        <a href="https://www.youtube.com/watch?v=${videoId}" target="_blank">
                            Watch video
                        </a>
                    </div>
                    <hr>
                `;
        });
    }

})
