<?php
require_once __DIR__ . "/VideoDTO.php";

class SimilarityCalculator {
    private function calculateTagRatio($video1, $video2) {
        $tags1 = array_map(fn($tag) => strtolower($tag), $video1->tags);
        $tags1 = array_unique(array_merge($tags1, $this->getKeywordsFromTitle($video1->title)));

        $tags2 = array_map(fn($tag) => strtolower($tag), $video2->tags);
        $tags2 = array_unique(array_merge($tags2, $this->getKeywordsFromTitle($video2->title)));

        $intersection = array_intersect($tags1, $tags2);

        if (min(count($tags1), count($tags2)) == 0) {
            return 0;
        }

        $safeguard = count($tags1) < 5 || count($tags2) < 5 ? 0.75 : 1;
        return $safeguard * ((count($intersection)) / min(count($tags1), count($tags2)));
    }

    private function calculateTopicRatio($video1, $video2) {
        $norm = fn($url) => basename(rtrim($url, '/'));

        $t1 = array_map($norm, $video1->topicCategories);
        $t2 = array_map($norm, $video2->topicCategories);

        $intersection = array_intersect($t1, $t2);
        $union = array_unique(array_merge($t1, $t2));

        if (count($union) == 0) {
            return 0;
        }

        return (count($intersection) / count($union));
    }

    private function calculateEngagementRatio($video1, $video2) {
        $commImportance = 1.5;

        if ($video1->viewCount <= 0 || $video2->viewCount <= 0) {
            return 0;
        }

        $ratio1 = ($video1->likeCount + (int)($video1->commCount * $commImportance)) / $video1->viewCount;
        $ratio2 = ($video2->likeCount + (int)($video2->commCount * $commImportance)) / $video2->viewCount;

        if ($ratio1 == 0 && $ratio2 == 0) {
            return 1;
        }

        if ($ratio1 == 0 || $ratio2 == 0) {
            return 0;
        }

        return min($ratio1, $ratio2) / max($ratio1, $ratio2);
    }

    private function calculateChannel($video1, $video2) {
        return $video1->channelId == $video2->channelId ? 1 : 0;
    }

    private function calculateLanguage($video1, $video2) {
        return
            substr($video1->audioLanguage, 0, 2)
            == substr($video2->audioLanguage, 0, 2) ? 1 : 0;
    }

    private function calculateDurationRatio($video1, $video2) {
        if (max($video1->durationSeconds, $video2->durationSeconds) == 0) return 0;
        return min($video1->durationSeconds, $video2->durationSeconds)
            / max($video1->durationSeconds, $video2->durationSeconds);
    }

    private function getCategoryMultiplier($video1, $video2) {
        return $video1->categoryId == $video2->categoryId ? 1 : 0.5;
    }

    private function getStopWords() {
        return [
            'the', 'and', 'for', 'with', 'this', 'that', 'from', 'your', 'have', 'was',
            'are', 'but', 'not', 'what', 'all', 'were', 'when', 'can', 'said', 'there',
            'use', 'each', 'which', 'how', 'their', 'will', 'other', 'about', 'then',
            'them', 'these', 'some', 'her', 'would', 'make', 'him', 'into', 'has', 'two',
            'a', 'an', 'of', 'in', 'on', 'at', 'by', 'to', 'as', 'it', 'is', 'be', 'or',

            'sau', 'dar', 'iar', 'insa', 'ori', 'cum', 'cand', 'unde', 'care', 'cine',
            'ceea', 'aceasta', 'acesta', 'acele', 'aceia', 'acestui', 'acestei', 'prin',
            'pentru', 'spre', 'catre', 'din', 'dintr', 'dinspre', 'este', 'sunt', 'fost',
            'era', 'sursa', 'acest', 'acestor', 'unui', 'unei', 'unor', 'prin', 'lui',
            'lor', 'tot', 'toate', 'multi', 'multe', 'niște', 'niste', 'vostru', 'noastre',

            'video', 'clip', 'official', 'original',
            'episode', 'part', 'capitol', 'episodul', 'sezon', 'season',
            'hd', '4k', '1080p', 'subtitles',
            'how', 'to', 'make', 'best', 'new', 'update', 'latest', 'shoutout', 'full', 'version',
            'edition', 'bonus'
        ];
    }

    private function getKeywordsFromTitle($title) {
        $stopWords = $this->getStopWords();

        $title = mb_strtolower($title, 'UTF-8');

        $transliterator = Transliterator::create('Any-Latin; Latin-ASCII');
        $title = $transliterator->transliterate($title);
        
        $cleanTitle = preg_replace('/[^\w\s]/u', '', $title);

        $words = explode(' ', $cleanTitle);

        $filtered = array_filter($words, function ($word) use ($stopWords) {
            $word = trim($word);
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });

        return array_values($filtered);
    }

    public function calculate($video1, $video2) {
        $weights = [
            'tags' => 40,
            'topics' => 20,
            'channel' => 10,
            'language' => 10,
            'duration' => 10,
            'engagement' => 10
        ];

        $score = 0;

        $score += $this->calculateTagRatio($video1, $video2) * $weights['tags'];
        $score += $this->calculateChannel($video1, $video2) * $weights['channel'];
        $score += $this->calculateTopicRatio($video1, $video2) * $weights['topics'];
        $score += $this->calculateLanguage($video1, $video2) * $weights['language'];
        $score += $this->calculateDurationRatio($video1, $video2) * $weights['duration'];
        $score += $this->calculateEngagementRatio($video1, $video2) * $weights['engagement'];

        return round($score * $this->getCategoryMultiplier($video1, $video2), 2);
    }
}