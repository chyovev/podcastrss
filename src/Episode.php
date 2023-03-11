<?php

namespace PodcastRSS;

class Episode
{

    /**
     * Title of the episode, required.
     * 
     * @var string
     */
    protected ?string $title = null;

    /**
     * Filesize of the episode (in bytes), required.
     * 
     * @var int
     */
    protected ?int $fileSize = null;
    
    /**
     * MIME type of the episode's file.
     * Supported file formats are:
     *     - audio/x-m4a
     *     - audio/mpeg
     *     - video/quicktime
     *     - video/mp4
     *     - video/x-m4v
     *     - application/pdf
     * 
     * @var string
     */
    protected ?string $mimeType = null;

    /**
     * Url of the episode, required.
     * Supported file formats correspond with
     * the MIME type:
     *     - m4a
     *     - mp3
     *     - mov
     *     - mp4
     *     - m4v
     *     - pdf
     * 
     * @var string
     */
    protected ?string $episodeUrl = null;


    ///////////////////////////////////////////////////////////////////////////
    public function getTitle(): ?string {
        return $this->title;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setTitle(string $title): self {
        $this->title = $title;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getFileSize(): ?int {
        return $this->fileSize;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setFileSize(int $fileSize): self {
        $this->fileSize = $fileSize;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getMimeType(): ?string {
        return $this->mimeType;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setMimeType(string $mimeType): self {
        $this->mimeType = $mimeType;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getEpisodeUrl(): ?string {
        return $this->episodeUrl;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setEpisodeUrl(string $episodeUrl): self {
        $this->episodeUrl = $episodeUrl;

        return $this;
    }
}