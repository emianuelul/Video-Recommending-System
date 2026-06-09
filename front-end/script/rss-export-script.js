import config from "./utils/config.js";

const statusText = document.getElementById("rss-status");
const linksContainer = document.getElementById("rss-links");

const feeds = {
    personal: {
        label: "Personal recommendations",
        endpoint: "/video/recommendations.php"
    },
    country: {
        label: "Country recommendations",
        endpoint: "/video/country-recommendations.php"
    },
    friends: {
        label: "Friends recommendations",
        endpoint: "/video/friends-recommendations.php"
    }
};

function getFeedUrl(feedKey) {
    const token = localStorage.getItem("token") || "";
    const feed = feeds[feedKey];
    const params = new URLSearchParams({
        format: "rss",
        token
    });

    return `${config.url_api}${feed.endpoint}?${params.toString()}`;
}

function setStatus(message) {
    statusText.textContent = message;
}

function renderLinks() {
    linksContainer.innerHTML = "";

    Object.keys(feeds).forEach(feedKey => {
        const row = document.createElement("div");
        const label = document.createElement("span");
        const input = document.createElement("input");

        row.className = "rss-link-row";
        label.textContent = feeds[feedKey].label;
        input.value = getFeedUrl(feedKey);
        input.readOnly = true;

        row.append(label, input);
        linksContainer.appendChild(row);
    });
}

async function copyFeed(feedKey) {
    const url = getFeedUrl(feedKey);

    try {
        await navigator.clipboard.writeText(url);
        setStatus("RSS link copied.");
    } catch (err) {
        console.error(err);
        setStatus("Could not copy the link.");
    }
}

function openFeed(feedKey) {
    window.open(getFeedUrl(feedKey), "_blank");
}

document.querySelectorAll("[data-feed]").forEach(button => {
    button.addEventListener("click", () => {
        openFeed(button.dataset.feed);
    });
});

document.querySelectorAll("[data-copy]").forEach(button => {
    button.addEventListener("click", () => {
        copyFeed(button.dataset.copy);
    });
});

new window.LogoutButton("logout");
renderLinks();
