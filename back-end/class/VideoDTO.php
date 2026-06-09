<?php

require_once __DIR__ . '/../../db/database.php';

class VideoDTO implements JsonSerializable {
    private $id;
    private $title;
    private $description;
    private $channelId;
    private $channelTitle;
    private $tags;
    private $durationSeconds;
    private $categoryId;
    private $viewCount;
    private $likeCount;
    private $commCount;
    private $topicCategories;
    private $audioLanguage;
    private $thumbnails;
    private $isLikedByUser;

    /**
     * @return mixed|string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed|string $id
     */
    public function setId($id): void {
        $this->id = $id;
    }

    /**
     * @return mixed|string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param mixed|string $title
     */
    public function setTitle($title): void {
        $this->title = $title;
    }

    /**
     * @return mixed|string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param mixed|string $description
     */
    public function setDescription($description): void {
        $this->description = $description;
    }

    /**
     * @return mixed|string
     */
    public function getChannelId() {
        return $this->channelId;
    }

    /**
     * @param mixed|string $channelId
     */
    public function setChannelId($channelId): void {
        $this->channelId = $channelId;
    }

    /**
     * @return mixed|string
     */
    public function getChannelTitle() {
        return $this->channelTitle;
    }

    /**
     * @param mixed|string $channelTitle
     */
    public function setChannelTitle($channelTitle): void {
        $this->channelTitle = $channelTitle;
    }

    /**
     * @return array|mixed
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * @param array|mixed $tags
     */
    public function setTags($tags): void {
        $this->tags = $tags;
    }

    /**
     * @return float|int
     */
    public function getDurationSeconds() {
        return $this->durationSeconds;
    }

    /**
     * @param float|int $durationSeconds
     */
    public function setDurationSeconds($durationSeconds): void {
        $this->durationSeconds = $durationSeconds;
    }

    /**
     * @return mixed|string
     */
    public function getCategoryId() {
        return $this->categoryId;
    }

    /**
     * @param mixed|string $categoryId
     */
    public function setCategoryId($categoryId): void {
        $this->categoryId = $categoryId;
    }

    public function getViewCount(): int {
        return $this->viewCount;
    }

    public function setViewCount(int $viewCount): void {
        $this->viewCount = $viewCount;
    }

    public function getLikeCount(): int {
        return $this->likeCount;
    }

    public function setLikeCount(int $likeCount): void {
        $this->likeCount = $likeCount;
    }

    public function getCommCount(): int {
        return $this->commCount;
    }

    public function setCommCount(int $commCount): void {
        $this->commCount = $commCount;
    }

    /**
     * @return array|mixed
     */
    public function getTopicCategories() {
        return $this->topicCategories;
    }

    /**
     * @param array|mixed $topicCategories
     */
    public function setTopicCategories($topicCategories): void {
        $this->topicCategories = $topicCategories;
    }

    /**
     * @return mixed|string
     */
    public function getAudioLanguage() {
        return $this->audioLanguage;
    }

    /**
     * @param mixed|string $audioLanguage
     */
    public function setAudioLanguage($audioLanguage): void {
        $this->audioLanguage = $audioLanguage;
    }

    /**
     * @return array|mixed
     */
    public function getThumbnails() {
        return $this->thumbnails;
    }

    /**
     * @param array|mixed $thumbnails
     */
    public function setThumbnails($thumbnails): void {
        $this->thumbnails = $thumbnails;
    }

    /**
     * @return mixed
     */
    public function getIsLikedByUser() {
        return $this->isLikedByUser;
    }

    /**
     * @param mixed $isLikedByUser
     */
    public function setIsLikedByUser($isLikedByUser): void {
        $this->isLikedByUser = $isLikedByUser;
    }

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

    /**
     * Reconstruct a VideoDTO from the flat shape produced by jsonSerialize()
     * (i.e. what the frontend sends back after storing the card data).
     */
    public static function fromSerialized(array $data): self {
        $dto = new self([
            'id' => $data['id'] ?? '',
            'snippet' => [
                'title'                => $data['title'] ?? '',
                'description'          => $data['description'] ?? '',
                'channelId'            => $data['channelId'] ?? '',
                'channelTitle'         => $data['channelTitle'] ?? '',
                'tags'                 => $data['tags'] ?? [],
                'categoryId'           => $data['categoryId'] ?? '',
                'thumbnails'           => $data['thumbnails'] ?? [],
                'defaultAudioLanguage' => $data['audioLanguage'] ?? '',
            ],
            // durationSeconds is already an int — skip ISO-8601 parsing
            'contentDetails' => ['duration' => 'PT0S'],
            'statistics' => [
                'viewCount'    => $data['viewCount'] ?? 0,
                'likeCount'    => $data['likeCount'] ?? 0,
                'commentCount' => $data['commCount'] ?? 0,
            ],
            'topicDetails' => [
                'topicCategories' => $data['topicCategories'] ?? [],
            ],
        ]);

        // Overwrite durationSeconds directly — the constructor parsed 'PT0S' above
        $dto->durationSeconds = (int)($data['durationSeconds'] ?? 0);
        $dto->isLikedByUser   = $data['isLikedByUser'] ?? false;

        return $dto;
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'channelId' => $this->channelId,
            'channelTitle' => $this->channelTitle,
            'tags' => $this->tags,
            'durationSeconds' => $this->durationSeconds,
            'categoryId' => $this->categoryId,
            'viewCount' => $this->viewCount,
            'likeCount' => $this->likeCount,
            'commCount' => $this->commCount,
            'topicCategories' => $this->topicCategories,
            'audioLanguage' => $this->audioLanguage,
            'thumbnails' => $this->thumbnails,
            'isLikedByUser' => $this->isLikedByUser,
        ];
    }
}