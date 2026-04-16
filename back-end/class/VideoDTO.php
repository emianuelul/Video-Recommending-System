<?php

class VideoDTO {
    public $id;
    public $title;
    public $description;
    public $channelId;
    public $channelTitle;
    public $tags;
    public $durationSeconds;
    public $categoryId;
    public $viewCount;
    public $likeCount;
    public $commCount;
    public $topicCategories;
    public $audioLanguage;
    public $thumbnails;

    /**
     * @throws Exception
     */
    private function convertToSeconds($duration) {
        try {
            $interval = new DateInterval($duration);

            return
                ($interval->d * 86400) +
                ($interval->h * 3600) +
                ($interval->i * 60) +
                $interval->s;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function __construct($data) {
        $this->id = $data['id'] ?? '';
        $this->title = $data['snippet']['title'] ?? '';
        $this->description = $data['snippet']['description'] ?? '';
        $this->channelId = $data['snippet']['channelId'] ?? '';
        $this->channelTitle = $data['snippet']['channelTitle'] ?? '';
        $this->tags = $data['snippet']['tags'] ?? [];
        $this->categoryId = $data['snippet']['categoryId'] ?? '';

        $this->durationSeconds = $this->convertToSeconds($data['contentDetails']['duration'] ?? '');

        $this->viewCount = (int)($data['statistics']['viewCount'] ?? 0);
        $this->likeCount = (int)($data['statistics']['likeCount'] ?? 0);
        $this->commCount = (int)($data['statistics']['commentCount'] ?? 0);

        $this->topicCategories = $data['topicDetails']['topicCategories'] ?? [];
        $this->thumbnails = $data['snippet']['thumbnails'] ?? [];

        $this->audioLanguage = $data['snippet']['defaultAudioLanguage'] ?? '';
    }
}