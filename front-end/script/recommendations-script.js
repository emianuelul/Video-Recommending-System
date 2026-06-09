import DOMStuff from "./DOMStuff.js";
import config from "./utils/config.js";

const forYouContainer = document.getElementById("recs-for-you");
const countryContainer = document.getElementById("recs-country");
const friendsContainer = document.getElementById("recs-friends");

function renderSkeletons(container, n = 4) {
  container.innerHTML = "";
  for (let i = 0; i < n; i++) {
    container.insertAdjacentHTML("beforeend", `
      <div class="skeleton-video-card" aria-hidden="true">
        <div class="skeleton skeleton-video-thumb"></div>
        <div class="skeleton skeleton-video-title"></div>
        <div class="skeleton skeleton-video-desc"></div>
        <div class="skeleton skeleton-video-desc short"></div>
      </div>
    `);
  }
}

function renderError(container, msg) {
  container.innerHTML = `<p class="rec-status error">${msg}</p>`;
}

function renderEmpty(container, msg) {
  container.innerHTML = `<p class="rec-status">${msg}</p>`;
}

async function fetchAndRender(endpoint, container, emptyMsg) {
  renderSkeletons(container);

  try {
    const res = await fetch(config.url_api + endpoint, {
      method: "GET",
      headers: {
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    container.innerHTML = "";

    if (!Array.isArray(data)) {
        renderError(container, data.error || data.message || "Failed to load.");
        return;
    }

    if (data.length === 0) {
      renderEmpty(container, emptyMsg);
      return;
    }

    data.forEach((video) => {
      container.appendChild(DOMStuff.createVideoCard(video));
    });

  } catch (err) {
    console.error(err);
    renderError(container, "Could not load recommendations.");
  }
}

async function loadAll() {
  Promise.all([
    fetchAndRender("/video/recommendations.php", forYouContainer, "No personal recommendations yet. Watch and like some videos!"),
    fetchAndRender("/video/country-recommendations.php", countryContainer, "No trending videos found for your region."),
    fetchAndRender("/video/friends-recommendations.php", friendsContainer, "Your friends haven't liked any videos yet.")
  ]);
}

loadAll();

if (typeof LogoutButton !== 'undefined') {
  new LogoutButton("logout");
}
