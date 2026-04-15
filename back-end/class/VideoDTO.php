<?php

class ResponseDTO {
    public $id;
    public $title;
    public $description;
    public $channelId;
    public $tags;
    public $durationSeconds;
    public $categoryId;

    public function __construct(array $data) {
        $this->id = isset($data['id']['videoId']) ? $data['id']['videoId'] : $data['id'];
        $this->title = isset($data['snippet']['title']) ? $data['snippet']['title'] : '';
        $this->description = isset($data['snippet']['description']) ? $data['snippet']['description'] : '';
        $this->channelId = isset($data['snippet']['channelId']) ? $data['snippet']['channelId'] : '';
        $this->durationSeconds =
            // ... restul campurilor
        $this->tags = isset($data['snippet']['tags']) ? $data['snippet']['tags'] : [];
    }
}