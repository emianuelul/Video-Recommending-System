<?php
require_once __DIR__ . "/../class/SimilarityCalculator.php";
require_once __DIR__ . "/../class/VideoDTO.php";

$calc = new SimilarityCalculator();
$json1 = <<<'JSON'
{
    "id": "3kiPX_bDX3M",
    "title": "Cozonac cu aluat oparit, cea mai cautata reteta pentru Paste #jamilacuisine",
    "description": "Cozonac cu aluat oparit, reteta pe care trebuie sa-i faceti de sarbatori! Acestia sunt cei mai mari si mai pufosi cozonaci pe care ii puteti incerca de Paste!  Cozonacii cu aluat oparit sunt de-a dreptul uriasi, se desfac in fasii, sunt umpluti cu de toate si se pastreaza proaspeti si moi zile in sir. Neaparat sa-i faceti si voi! O sa va placa!\n\n#cozonac  #cozonaccualuatoparit #celmaibuncozonac #cozonacpufos  #viralvideos #shortvideo",
    "channelId": "UCR7biAlnpQsATX5mPJ5Ldkw",
    "channelTitle": "JamilaCuisine",
    "tags": [
      "cozonac",
      "cozonac cu aluat oparit",
      "cel mai bun cozonac",
      "cozonac traditional",
      "retete de cozonac",
      "cozonac de casa",
      "cozonac pas cu pas"
    ],
    "durationSeconds": 175,
    "categoryId": "26",
    "viewCount": 301509,
    "likeCount": 4364,
    "commCount": 146,
    "topicCategories": [
      "https://en.wikipedia.org/wiki/Food",
      "https://en.wikipedia.org/wiki/Lifestyle_(sociology)"
    ],
    "audioLanguage": "ro",
    "thumbnails": {
      "default": {
        "url": "https://i.ytimg.com/vi/3kiPX_bDX3M/default.jpg",
        "width": 120,
        "height": 90
      },
      "medium": {
        "url": "https://i.ytimg.com/vi/3kiPX_bDX3M/mqdefault.jpg",
        "width": 320,
        "height": 180
      },
      "high": {
        "url": "https://i.ytimg.com/vi/3kiPX_bDX3M/hqdefault.jpg",
        "width": 480,
        "height": 360
      },
      "standard": {
        "url": "https://i.ytimg.com/vi/3kiPX_bDX3M/sddefault.jpg",
        "width": 640,
        "height": 480
      },
      "maxres": {
        "url": "https://i.ytimg.com/vi/3kiPX_bDX3M/maxresdefault.jpg",
        "width": 1280,
        "height": 720
      }
    }
  }
JSON;

$json2 = <<<'JSON'
{
    "id": "qWI_oxQQnR8",
    "title": "Cozonac simplu cu nucă și rahat - rețeta de cozonac pufos uriaș cu care NU dai greș | Savori Urbane",
    "description": "Am creat acest cozonac simplu cu nucă și rahat pentru a veni și ajutorul celor care sunt începători în bucătărie. Este un cozonac uriaș cu miez pufos, care se desface în caiere (pale, fâșii), cu suficientă umplutură cremoasă de nucă, rahat, stafide, coji confiate de portocală. Veți vedea cât de ușor se lucrează aluatul și cum se face o cremă de nucă opărită cu lapte. Am decis să fac un singur cozonac uriaș, copt în formă mare, pentru a ne bucura de cât mai mult miez moale și gustos! Este echilibrat și foarte reușit din toate punctele de vedere.\nNu este greșit dacă faceți 2 cozonaci mai mici, copți în forme mai înguste (împărțind în 4 aluatul și umplutura). \nRețeta scrisă este aici: https://savoriurbane.com/cozonac-simplu-cu-nuca-si-rahat-reteta-video-de-cozonac-urias-care-reuseste-si-incepatorilor/\n***INGREDIENTE pentru un cozonac simplu uriaș (tavă de 31x16x10 cm - măsurată sus, la buză)\n*ALUAT de cozonac\n750 g făină pentru cozonac\n7 g drojdie uscată sau 25 g proaspătă\n225 ml lapte călduț (plus rezervă)\n100 g zahăr\n4 gălbenușuri \n100 g smântână grasă fermentată (cu 24% grăsime)\n100 g unt 82%\ncojile rase de la 1 lămâie și 1 portocală\n1 plic zahăr vanilat\nmin. 8 g sare (eu pun 12 g)\n***UMPLUTURA\n*CREMA de nucă\n250 g nucă măcinată\n15 g cacao\n75 g zahăr\n1 plic zahăr vanilat\n75 ml lapte\nun praf de sare\n*ÎN plus\n250-300 g rahat\n50-100 g coajă confiată de portocale\n100 g stafide sau merișoare înmuiate în 150 ml suc de portocale + lichior, rom sau cognac (brandy, vinars)\n*1 ou pentru uns\n2 linguri de ulei pentru masa de lucru\nVă recomand și alte rețete video de cozonaci: https://www.youtube.com/playlist?list=PLVcTzPHC7F4YU5OsZ3X943zEy7-ub0s6w\nhttps://youtu.be/3C9uxkjZxeo\nhttps://youtu.be/vvjilbonPcE\nhttps://youtu.be/No3EQZD1p5M\nhttps://youtu.be/mF_J0ofBqaE\nhttps://youtu.be/A1-klcDzvBw\nPanettone: https://savoriurbane.com/panettone-cozonac-italian/\nPandoro: https://savoriurbane.com/pandoro-reteta-autentica-italiana-de-cozonac-auriu-extra-pufos/\nȘi să nu uit de muzica pomenită: Pink Floyd - Signs of Life https://youtu.be/Wuo9og4lFFg?si=Kj2a0qLFEUmrNutV\n_________________________________________________________________________\nNu uita să te abonezi la canalul nostru! Săptămânal vom publica rețete video!\nUrmărește-ne și pe:\nFacebook: https://www.facebook.com/savoriurbane1/\nInstagram: https://www.instagram.com/savori_urbane/\nTikTok: https://www.tiktok.com/@savoriurbane\nBlog: https://savoriurbane.com/",
    "channelId": "UCDwIAEZpZvBBv4948Q1GqYQ",
    "channelTitle": "Savori Urbane",
    "tags": [
      "savori urbane",
      "reteta savori urbane",
      "retete savori",
      "savori",
      "reteta savori",
      "oana savori",
      "cozonac",
      "cozonaci",
      "cozonac pufos",
      "cozonac urias",
      "cozonac simplu",
      "cozonac pufos cu nuca",
      "cozonac cu nuca",
      "cozonac cu rahat",
      "cozonac cu nuca si rahat",
      "cozonac simplu cu nuca si rahat",
      "aluat de cozonac",
      "cozonaci cu nuca",
      "cozonaci pufosi",
      "cozonac cu crema de nuca",
      "reteta cozonac",
      "cozonac incepatori",
      "cozonaci incepatori",
      "cozonac reteta simpla",
      "cozonac simplu cu nuca",
      "craciun",
      "pasti",
      "crema nuca"
    ],
    "durationSeconds": 2431,
    "categoryId": "26",
    "viewCount": 310444,
    "likeCount": 6668,
    "commCount": 500,
    "topicCategories": [
      "https://en.wikipedia.org/wiki/Food",
      "https://en.wikipedia.org/wiki/Lifestyle_(sociology)"
    ],
    "audioLanguage": "ro",
    "thumbnails": {
      "default": {
        "url": "https://i.ytimg.com/vi/qWI_oxQQnR8/default.jpg",
        "width": 120,
        "height": 90
      },
      "medium": {
        "url": "https://i.ytimg.com/vi/qWI_oxQQnR8/mqdefault.jpg",
        "width": 320,
        "height": 180
      },
      "high": {
        "url": "https://i.ytimg.com/vi/qWI_oxQQnR8/hqdefault.jpg",
        "width": 480,
        "height": 360
      },
      "standard": {
        "url": "https://i.ytimg.com/vi/qWI_oxQQnR8/sddefault.jpg",
        "width": 640,
        "height": 480
      },
      "maxres": {
        "url": "https://i.ytimg.com/vi/qWI_oxQQnR8/maxresdefault.jpg",
        "width": 1280,
        "height": 720
      }
    }
  }
JSON;

$data1 = json_decode($json1, true);
$data2 = json_decode($json2, true);

$wrapForDTO = function ($flatData) {
    return [
        'id' => $flatData['id'] ?? '',
        'snippet' => [
            'title' => $flatData['title'] ?? '',
            'description' => $flatData['description'] ?? '',
            'channelId' => $flatData['channelId'] ?? '',
            'channelTitle' => $flatData['channelTitle'] ?? '',
            'tags' => $flatData['tags'] ?? [],
            'categoryId' => $flatData['categoryId'] ?? '',
            'thumbnails' => $flatData['thumbnails'] ?? [],
            'defaultAudioLanguage' => $flatData['audioLanguage'] ?? ''
        ],
        'contentDetails' => [
            // DTO-ul tău folosește convertToSeconds pe acest string
            // Dacă ai deja secundele, le trimitem într-un format ISO8601 simulat
            'duration' => 'PT' . ($flatData['durationSeconds'] ?? 0) . 'S'
        ],
        'statistics' => [
            'viewCount' => $flatData['viewCount'] ?? 0,
            'likeCount' => $flatData['likeCount'] ?? 0,
            'commentCount' => $flatData['commCount'] ?? 0
        ],
        'topicDetails' => [
            'topicCategories' => $flatData['topicCategories'] ?? []
        ]
    ];
};

$v1 = new VideoDTO($wrapForDTO($data1));
$v2 = new VideoDTO($wrapForDTO($data2));

header("Content-Type: application/json");
echo $calc->calculate($v1, $v2);

