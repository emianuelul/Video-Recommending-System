import config from "./utils/config.js";

export default class DOMStuff {

    static createPasswordInput() {
        const label = document.createElement('label');
        label.textContent = "Password";
        label.setAttribute("for", "password")

        const img = document.createElement('img');
        img.src = "../libs/images/auth/lock-icon.svg";
        img.classList.add('passwordVis');

        const input = document.createElement('input');
        input.type = 'password';
        input.id = 'password';
        input.name = 'password';
        input.placeholder = "Password";
        input.required = true;

        return [label, img, input];
    }
    
    static createUsernameInput() {
        const label = document.createElement('label');
        label.textContent = "Username";
        label.setAttribute("for", "username")

        const img = document.createElement('img');
        img.src = "../libs/images/auth/user-icon.svg";

        const input = document.createElement('input');
        input.type = 'text';
        input.id = 'username';
        input.name = 'username';
        input.placeholder = "Username";
        input.maxLength = 30;
        input.required = true;

        return [label, img, input];
    }

    static createVideoCard(video) {
        const {
            thumbnail,
            title,
            description,
            videoId,
            isLikedByUser
        } = video;

        const card = document.createElement('div');
        card.classList.add('video-card');

        const thumb = document.createElement('img');
        thumb.src = thumbnail;
        thumb.alt = title;
        thumb.classList.add('video-thumbnail');

        const titleEl = document.createElement('h3');
        titleEl.textContent = title;
        titleEl.classList.add('video-title');

        const desc = document.createElement('p');
        desc.classList.add('video-description');

        const maxChars = 45;
        let expanded = false;

        const shortText =
            description.length > maxChars
                ? description.slice(0, maxChars) + "..."
                : description;

        desc.textContent = shortText;

        desc.addEventListener('click', () => {
            expanded = !expanded;

            desc.textContent = expanded
                ? description
                : shortText;

            desc.classList.toggle('expanded', expanded);
        });

        const likeBtn = document.createElement('button');
        likeBtn.classList.add('like-button');
        likeBtn.textContent = isLikedByUser ? '♥': '♡';
        
        let liked = isLikedByUser;

        likeBtn.addEventListener('click', async () => {
            try {
                if (!liked) {
                    const response = await fetch(config.url_api + '/video/like.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${localStorage.getItem("token")}`
                        },
                        body: JSON.stringify({ videoId })
                    });

                    if (!response.ok) {
                        throw new Error('Failed to like video');
                    }

                    liked = true;

                } else {

                    const response = await fetch(config.url_api + '/video/like.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${localStorage.getItem("token")}`
                        },
                        body: JSON.stringify({
                            videoId,
                            tags: video.tags || [],
                            categoryId: video.categoryId
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Failed to remove like');
                    }

                    liked = false;
                }

                likeBtn.textContent = liked ? '♥': '♡';
                likeBtn.classList.toggle('liked', liked);

            } catch (err) {
                console.error(err);
            }
        });

        const link = document.createElement('a');
        link.href = `https://www.youtube.com/watch?v=${videoId}`;
        link.target = '_blank';
        link.textContent = 'Watch Video';
        link.classList.add('video-link');

        card.append(
            thumb,
            titleEl,
            desc,
            likeBtn,
            link
        );

        return card;
    }
}