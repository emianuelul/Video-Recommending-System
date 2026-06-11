import config from "./utils/config.js";

export default class DOMStuff {
  static createPasswordInput() {
    const label = document.createElement("label");
    label.textContent = "Password";
    label.setAttribute("for", "password");

    const img = document.createElement("img");
    img.src = "../libs/images/auth/lock-icon.svg";
    img.classList.add("passwordVis");

    const input = document.createElement("input");
    input.type = "password";
    input.id = "password";
    input.name = "password";
    input.placeholder = "Password";
    input.required = true;

    return [label, img, input];
  }

  static createUsernameInput() {
    const label = document.createElement("label");
    label.textContent = "Username";
    label.setAttribute("for", "username");

    const img = document.createElement("img");
    img.src = "../libs/images/auth/user-icon.svg";

    const input = document.createElement("input");
    input.type = "text";
    input.id = "username";
    input.name = "username";
    input.placeholder = "Username";
    input.maxLength = 30;
    input.required = true;

    return [label, img, input];
  }

  static createVideoCard(video) {
    const videoId = video.id;
    const title = video.title || "";
    const description = video.description || "";
    const thumbnail = video.thumbnails?.medium?.url || video.thumbnails?.default?.url || "";
    const isLikedByUser = video.isLikedByUser;

    const card = document.createElement("div");
    card.classList.add("video-card");

    const thumb = document.createElement("img");
    thumb.src = thumbnail;
    thumb.alt = title;
    thumb.classList.add("video-thumbnail");

    const titleEl = document.createElement("h3");
    titleEl.textContent = title;
    titleEl.classList.add("video-title");

    const desc = document.createElement("p");
    desc.classList.add("video-description");

    const maxChars = 45;
    const shortText =
      description.length > maxChars
        ? description.slice(0, maxChars) + "..."
        : description;

    desc.textContent = shortText;

    const likeBtn = document.createElement("button");
    likeBtn.classList.add("like-button");
    likeBtn.textContent = isLikedByUser ? "♥" : "♡";

    let liked = isLikedByUser;

    likeBtn.addEventListener("click", async () => {
      try {
        if (!liked) {
          const response = await fetch(config.url_api + "/video/like.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
            body: JSON.stringify(video),
          });

          if (!response.ok) {
            throw new Error("Failed to like video");
          }

          liked = true;
        } else {
          const response = await fetch(config.url_api + "/video/like.php", {
            method: "DELETE",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
            body: JSON.stringify(video),
          });

          if (!response.ok) {
            throw new Error("Failed to remove like");
          }

          liked = false;
        }

        likeBtn.textContent = liked ? "♥" : "♡";
        likeBtn.classList.toggle("liked", liked);
        video.isLikedByUser = liked;
      } catch (err) {
        console.error(err);
      }
    });

    const watchUrl = `/page/watch.html?v=${videoId}`;

    thumb.style.cursor = "pointer";
    thumb.addEventListener("click", () => {
      sessionStorage.setItem("watchVideo", JSON.stringify(video));
      window.location.href = watchUrl;
    });

    titleEl.style.cursor = "pointer";
    titleEl.addEventListener("click", () => {
      sessionStorage.setItem("watchVideo", JSON.stringify(video));
      window.location.href = watchUrl;
    });

    const link = document.createElement("a");
    link.href = watchUrl;
    link.textContent = "Watch";
    link.classList.add("video-link");
    link.addEventListener("click", (e) => {
      e.preventDefault();
      sessionStorage.setItem("watchVideo", JSON.stringify(video));
      window.location.href = watchUrl;
    });

    card.append(thumb, titleEl, desc, likeBtn, link);

    return card;
  }
}
