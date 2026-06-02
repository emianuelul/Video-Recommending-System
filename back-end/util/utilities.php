<?php

require_once __DIR__ . '/../../db/database.php';

$categoriesList = [
    1 => "Film & Animation",
    2 => "Autos & Vehicles",
    10 => "Music",
    15 => "Pets & Animals",
    17 => "Sports",
    18 => "Short Movies",
    19 => "Travel & Events",
    20 => "Gaming",
    21 => "Videoblogging",
    22 => "People & Blogs",
    23 => "Comedy",
    24 => "Entertainment",
    25 => "News & Politics",
    26 => "Howto & Style",
    27 => "Education",
    28 => "Science & Technology",
    29 => "Nonprofits & Activism",
    30 => "Movies",
    31 => "Anime/Animation",
    32 => "Action/Adventure",
    33 => "Classics",
    34 => "Comedy",
    35 => "Documentary",
    36 => "Drama",
    37 => "Family",
    38 => "Foreign",
    39 => "Horror",
    40 => "Sci-Fi/Fantasy",
    41 => "Thriller",
    42 => "Shorts",
    43 => "Shows",
    44 => "Trailers"
];

$AVAILABLE_HOURS = 24;
$DECAY_HALF_LIFE_HOURS = 720;

function decayUserWeights($userId) {
    global $db;
    $affected = 0;
    $now = time();

    $tables = ['user_tags', 'user_categories'];
    foreach ($tables as $table) {
        $select = $db->prepare("SELECT id, weight, last_interacted_at FROM {$table} WHERE user_id = :user_id");
        $select->execute([':user_id' => $userId]);
        $update = $db->prepare("UPDATE {$table} SET weight = :weight WHERE id = :id");

        while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
            $elapsedH = ($now - strtotime($row['last_interacted_at'])) / 3600;
            $newWeight = max(0, $row['weight'] - 1);
            if ($newWeight === (int)$row['weight']) {
                continue;
            }
            $update->execute([':weight' => $newWeight, ':id' => $row['id']]);
            $affected += $update->rowCount();
        }
    }
}