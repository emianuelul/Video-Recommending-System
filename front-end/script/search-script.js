import DOMStuff from "./DOMStuff.js";

const searchBar = document.querySelector("#search-bar");
const videoDurationSelect = document.querySelector("#video-duration");
const afterTime = document.querySelector("#after-time");
const beforeTime = document.querySelector("#before-time");
const languageSelect = document.querySelector("#language-select");
const orderSelect = document.querySelector("#order-select");
const searchBtn = document.querySelector(".search-btn");
const results = document.querySelector(".result");

function convertToRfc3339(dateString) {
  let output = "";

  if (dateString !== "") {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0"); // e 0 indexed lmao ?????
    const day = String(date.getDate()).padStart(2, "0");

    output = `${year}-${month}-${day}T00:00:00Z`;
  }

  return output;
}

const url = "http://localhost:8081/api/video/search.php";

searchBtn.addEventListener("click", async () => {
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

    let resultsDiv = document.getElementById("results");
    let data = await fetch(url + "?" + `${queryParams}`, {
      method: "GET",
      headers: {
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
      credentials: "include",
    }).then((res) => res.json());

    resultsDiv.innerHTML = "";

    if (!data || data.length === 0) {
      resultsDiv.innerHTML = "<p>No results found.</p>";
      return;
    }

    data.forEach((video) => {
      resultsDiv.appendChild(DOMStuff.createVideoCard(video));
    });
  }
});
