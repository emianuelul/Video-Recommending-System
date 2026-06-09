import config from "./utils/config.js";

const params   = new URLSearchParams(window.location.search);
const videoId  = params.get("v");
let   video    = null;

try {
  const raw = sessionStorage.getItem("watchVideo");
  if (raw) video = JSON.parse(raw);
} catch (_) { /* ignore */ }

if (!videoId) {
  window.location.href = "/page/search.html";
}

const embedRatio    = document.getElementById("watch-embed-ratio");
const metaTitle     = document.getElementById("watch-meta-title");
const metaDesc      = document.getElementById("watch-meta-desc");
const metaExpand    = document.getElementById("watch-meta-expand");
const likeBtn       = document.getElementById("watch-like-btn");
const ytLink        = document.getElementById("watch-yt-link");
const sidebarList   = document.getElementById("watch-sidebar-list");
const sidebarStatus = document.getElementById("watch-sidebar-status");

function buildEmbed(id) {
  const iframe = document.createElement("iframe");
  iframe.src = `https://www.youtube.com/embed/${id}?rel=0&modestbranding=1`;
  iframe.title = "YouTube video player";
  iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share";
  iframe.allowFullscreen = true;
  embedRatio.appendChild(iframe);
}

buildEmbed(videoId);

if (video) {
  document.title = `${video.title} — SIV`;
  metaTitle.textContent = video.title;

  const fullDesc = video.description || "";
  metaDesc.textContent = fullDesc;

  if (fullDesc.length > 200) {
    metaDesc.classList.add("collapsed");
    metaExpand.hidden = false;
    let expanded = false;
    metaExpand.addEventListener("click", () => {
      expanded = !expanded;
      metaDesc.classList.toggle("collapsed", !expanded);
      metaExpand.textContent = expanded ? "Show less" : "Show more";
    });
  } else {
    metaExpand.hidden = true;
  }

  ytLink.href = `https://www.youtube.com/watch?v=${videoId}`;

  let liked = video.isLikedByUser ?? false;
  likeBtn.textContent = liked ? "♥ Liked" : "♡ Like";
  likeBtn.classList.toggle("liked", liked);

  likeBtn.addEventListener("click", async () => {
    const method = liked ? "DELETE" : "POST";
    try {
      const res = await fetch(config.url_api + "/video/like.php", {
        method,
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
        body: JSON.stringify(video),
      });
      if (!res.ok) throw new Error("Request failed");
      liked = !liked;
      likeBtn.textContent = liked ? "♥ Liked" : "♡ Like";
      likeBtn.classList.toggle("liked", liked);
      video.isLikedByUser = liked;
      sessionStorage.setItem("watchVideo", JSON.stringify(video));
    } catch (err) {
      console.error(err);
    }
  });
} else {
  document.title = "Watch — SIV";
  metaTitle.textContent = "Video";
  metaDesc.textContent = "";
  metaExpand.hidden = true;
  ytLink.href = `https://www.youtube.com/watch?v=${videoId}`;
}

function renderSkeletons(n = 5) {
  sidebarList.innerHTML = "";
  for (let i = 0; i < n; i++) {
    sidebarList.insertAdjacentHTML("beforeend", `
      <div class="skeleton-card" aria-hidden="true">
        <div class="skeleton skeleton-thumb"></div>
        <div class="skeleton-lines">
          <div class="skeleton skeleton-line"></div>
          <div class="skeleton skeleton-line short"></div>
        </div>
      </div>
    `);
  }
}

function createSidebarCard(v) {
  const card = document.createElement("a");
  card.classList.add("watch-sidebar-card");
  card.href = `/page/watch.html?v=${v.id}`;
  card.setAttribute("aria-label", `Watch: ${v.title}`);

  card.addEventListener("click", (e) => {
    e.preventDefault();
    sessionStorage.setItem("watchVideo", JSON.stringify(v));
    window.location.href = `/page/watch.html?v=${v.id}`;
  });

  const thumb = document.createElement("img");
  thumb.src = v.thumbnails?.medium?.url || v.thumbnails?.default?.url || "";
  thumb.alt = v.title;
  thumb.classList.add("watch-sidebar-thumb");
  thumb.loading = "lazy";

  const info = document.createElement("div");
  info.classList.add("watch-sidebar-info");

  const title = document.createElement("p");
  title.classList.add("watch-sidebar-title");
  title.textContent = v.title;

  const meta = document.createElement("span");
  meta.classList.add("watch-sidebar-meta");
  const channel = v.channelTitle || v.channel_title || "";
  meta.textContent = channel;

  info.append(title, meta);
  card.append(thumb, info);
  return card;
}

async function loadSimilar() {
  if (!video) {
    sidebarStatus.textContent = "No video data — can't load similar videos.";
    sidebarStatus.hidden = false;
    return;
  }

  renderSkeletons(6);
  sidebarStatus.hidden = true;

  try {
    const res = await fetch(config.url_api + "/video/similar-to.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
      body: JSON.stringify({ video }),
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    sidebarList.innerHTML = "";

    const similar = data.similar ?? [];
    if (similar.length === 0) {
      sidebarStatus.textContent = "No similar videos found.";
      sidebarStatus.hidden = false;
      return;
    }

    similar.forEach((v) => {
      sidebarList.appendChild(createSidebarCard(v));
    });

  } catch (err) {
    console.error(err);
    sidebarList.innerHTML = "";
    sidebarStatus.textContent = "Couldn't load similar videos.";
    sidebarStatus.classList.add("error");
    sidebarStatus.hidden = false;
  }
}

loadSimilar();

new LogoutButton("logout");
