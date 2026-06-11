<div align="center">

  <h1>Video Recommending System</h1>

  <p>
    Aplicatie web pentru cautarea, vizionarea si recomandarea de videoclipuri YouTube
    pe baza preferintelor utilizatorului, istoricului de interactiuni si activitatii prietenilor.
  </p>

  <h4>
    <a href="front-end/index.html">Landing Page</a>
    <span> · </span>
    <a href="front-end/page/documentation.html">SRS Documentation</a>
    <span> · </span>
    <a href="front-end/page/login.html">Login</a>
    <span> · </span>
    <a href="front-end/page/register.html">Register</a>
  </h4>

</div>

<br>

# Table of Contents

- [About the Project](#about-the-project)
  - [Documentation](#documentation)
  - [Tech Stack](#tech-stack)
  - [Features](#features)
  - [Project Structure](#project-structure)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Run Locally](#run-locally)
  - [Configuration](#configuration)
- [Usage](#usage)
- [Main API Areas](#main-api-areas)
- [Database](#database)
- [Notes](#notes)

## About the Project

Video Recommending System, afisat in interfata ca SIV, este o aplicatie web care ajuta utilizatorii sa gaseasca videoclipuri relevante. Sistemul foloseste YouTube Data API pentru cautare si metadate video, iar apoi construieste recomandari pe baza tagurilor, categoriilor, limbii, duratei, istoricului de aprecieri si prietenilor.

Aplicatia include si functionalitati de socializare prin cereri de prietenie, export RSS pentru recomandari si o zona de administrare pentru gestionarea conturilor.

### Documentation

Raportul principal al proiectului este disponibil ca pagina separata a site-ului:

```text
front-end/page/documentation.html
```

Documentatia este scrisa in format HTML si urmareste structura unui Software Requirements Specification, inspirata de IEEE System Requirements Specification Template.

### Tech Stack

<details>
  <summary>Client</summary>
  <ul>
    <li>HTML</li>
    <li>CSS</li>
    <li>JavaScript</li>
  </ul>
</details>

<details>
  <summary>Server</summary>
  <ul>
    <li>PHP</li>
    <li>PHP built-in development server</li>
  </ul>
</details>

<details>
  <summary>Database</summary>
  <ul>
    <li>SQLite</li>
  </ul>
</details>

<details>
  <summary>External API</summary>
  <ul>
    <li>YouTube Data API</li>
  </ul>
</details>

### Features

- User register si login cu token de sesiune.
- Cautare videoclipuri YouTube cu filtre.
- Pagina de vizionare cu recomandari similare.
- Like/unlike pentru videoclipuri.
- Recomandari personalizate pe baza profilului utilizatorului.
- Recomandari populare in tara utilizatorului.
- Recomandari bazate pe activitatea prietenilor.
- Cereri de prietenie, accept/refuse, listare si remove friend.
- Export RSS pentru recomandari.
- Pagina de cont pentru date personale, schimbare parola si stergere cont.
- Admin login separat pentru schimbare parole si stergere conturi de user.

### Project Structure

```text
back-end/
  api/
    admin/
    auth/
    friends/
    user/
    video/
  class/
  util/
  router.php

db/
  database.php

front-end/
  index.html
  page/
  script/
  styles/
```

## Getting Started

### Prerequisites

Pentru rulare locala ai nevoie de:

- PHP instalat local;
- extensia SQLite activa in PHP;
- conexiune la internet pentru YouTube Data API;
- o cheie YouTube API configurata in backend.

### Run Locally

Pornire cu script:

```bash
./start.sh
```

Pornire manuala:

```bash
php -S localhost:8081 -t back-end back-end/router.php
php -S localhost:8001 -t front-end
```

Aplicatia va fi disponibila la:

```text
Frontend: http://localhost:8001
Backend:  http://localhost:8081
```

### Configuration

Configuratia principala se afla in:

```text
back-end/config.php
```

Variabile importante:

```php
YT_API_KEY
MAX_SEARCH_RESULTS
```

## Usage

1. Deschide `http://localhost:8001`.
2. Creeaza un cont din pagina Register.
3. Alege preferinte initiale precum categorii, limbi, durata si tara.
4. Foloseste pagina Search pentru cautare video.
5. Apasa Like pe videoclipuri pentru a imbunatati recomandarile.
6. Acceseaza Recommendations pentru feeduri personalizate.
7. Foloseste Friends pentru cereri de prietenie.
8. Foloseste RSS Export pentru linkuri RSS.
9. Foloseste Account pentru schimbare parola sau stergere cont.

## Main API Areas

| Area | Path | Purpose |
| --- | --- | --- |
| Auth | `back-end/api/auth` | Register si login user |
| Video | `back-end/api/video` | Search, recommendations, like, RSS |
| Friends | `back-end/api/friends` | Friend requests si friends |
| User | `back-end/api/user` | Account details, change password, delete account |
| Admin | `back-end/api/admin` | Admin login si user management |

## Database

Schema este initializata in:

```text
db/database.php
```

Tabele principale:

- `users`
- `user_tokens`
- `admins`
- `admin_tokens`
- `user_preferences`
- `user_tags`
- `user_categories`
- `friends`
- `user_likes`
- `interacted_videos`

## Notes

- Parolele sunt salvate folosind `password_hash`.
- Token-urile sunt folosite pentru autorizarea requesturilor protejate.
- Baza SQLite este potrivita pentru dezvoltare si demo local.
- Pentru productie, cheia YouTube API ar trebui mutata intr-un mecanism de configurare securizat.
