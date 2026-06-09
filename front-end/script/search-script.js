import DOMStuff from "./DOMStuff.js";
import config from "./utils/config.js";

const searchBar = document.querySelector("#search-bar");
const videoDurationSelect = document.querySelector("#video-duration");
const afterTime = document.querySelector("#after-time");
const beforeTime = document.querySelector("#before-time");
const languageSelect = document.querySelector("#language-select");
const orderSelect = document.querySelector("#order-select");
const searchBtn = document.querySelector(".search-btn");
const results = document.querySelector(".result");
const searchForm = document.querySelector("#search-form");
const statusText = document.querySelector("#search-status");

function convertToRfc3339(dateString) {
  let output = "";

  if (dateString !== "") {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0"); 
    const day = String(date.getDate()).padStart(2, "0");

    output = `${year}-${month}-${day}T00:00:00Z`;
  }

  return output;
}

const url = config.url_api + "/video/search.php";

async function runSearch() {
  const q = searchBar.value.trim();
  const videoDuration = videoDurationSelect.value;
  const publishedAfter = convertToRfc3339(afterTime.value);
  const publishedBefore = convertToRfc3339(beforeTime.value);
  const relevanceLanguage = languageSelect.value;
  const order = orderSelect.value;

  if (q !== "") {
    const queryParams = new URLSearchParams();
    queryParams.set("q", q);

    if (videoDuration !== "") {
      queryParams.set("videoDuration", videoDuration);
    }
    if (publishedAfter !== "") {
      queryParams.set("publishedAfter", publishedAfter);
    }
    if (publishedBefore !== "") {
      queryParams.set("publishedBefore", publishedBefore);
    }
    if (relevanceLanguage !== "") {
      queryParams.set("relevanceLanguage", relevanceLanguage);
    }
    if (order !== "") {
      queryParams.set("order", order);
    }

    let resultsDiv = document.getElementById("results");
    resultsDiv.innerHTML = "";
    statusText.textContent = "Searching...";

    try {
      let data = await fetch(`${url}?${queryParams.toString()}`, {
        method: "GET",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
        credentials: "include",
      }).then((res) => res.json());

      resultsDiv.innerHTML = "";

      if (!Array.isArray(data)) {
        statusText.textContent = data.message || data.error || "Search failed.";
        return;
      }

      if (data.length === 0) {
        statusText.textContent = "No results found.";
        return;
      }

      statusText.textContent = `Found ${data.length} video(s)`;

      data.forEach((video) => {
        resultsDiv.appendChild(DOMStuff.createVideoCard(video));
      });
    } catch (err) {
      console.error(err);
      statusText.textContent = "Search failed. Check the backend server.";
    }
  } else {
    statusText.textContent = "Type something to search.";
  }
}

searchBtn.addEventListener("click", runSearch);

searchForm.addEventListener("submit", event => {
  event.preventDefault();
  runSearch();
});

new LogoutButton("logout");
