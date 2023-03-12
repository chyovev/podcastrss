<?php

namespace PodcastRSS;

use DateTime;
use Exception;
use TypeError;

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

    /**
     * Each episode should have a unique GUID which does not
     * change, even if the episode's metadata does change.
     * Not required, but recommended.
     * 
     * @var string
     */
    protected ?string $guid = null;

    /**
     * The date and time when the episode was released.
     * When serialized, the date should be formatted using the
     * RFC 2822 specifications, e.g.: Sun, 12 Mar 2023 12:10:00 GMT.
     * Not required, but recommended.
     * 
     * @var DateTime
     */
    protected ?DateTime $pubDate = null;

    /**
     * The episode's description, max size is 4000 bytes (~3600 chars).
     * Rich text formatting supported (<p>, <ol>, <ul>, <li>, <a>).
     * Not required, but recommended.
     * 
     * @var string
     */
    protected ?string $description = null;

    /**
     * Duration of the episode (in seconds).
     * Not required, but recommended.
     * 
     * @var int
     */
    protected ?int $duration = null;

    /**
     * A webpage corresponding with the episode (if any).
     * Not to be confused with the episode's URL.
     * Field is optional.
     * 
     * @var string
     */
    protected ?string $website = null;

    /**
     * Artwork of the episode, JPEG or PNG, 72 dpi, RGB colorspace. 
     * Min size: 1400x1400 px, max size: 3000x3000 px.
     * Field is optional.
     * 
     * @var string
     */
    protected ?string $imageUrl = null;

    /**
     * Whether the episode itself is explicit.
     * Unlike the podcast $isExplicit property,
     * the episode one is optional.
     * 
     * @var bool 
     */
    protected ?bool $isExplicit = null;

    /**
     * Whether a single episode from the podcast should
     * be removed from the platforms, default value is false.
     * Field is optional.
     * 
     * @var bool
     */
    protected bool $shouldBeRemoved = false;


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

    ///////////////////////////////////////////////////////////////////////////
    public function getGuid(): ?string {
        return $this->guid;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setGuid(string $guid): self {
        $this->guid = $guid;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getPubDate(): ?DateTime {
        return $this->pubDate;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Set the publication date of the episode.
     * The parameter can either be a DateTime object
     * or a parsable date string (such as ISO 8601 format).
     * 
     * @see https://www.php.net/manual/en/class.datetimeinterface.php#datetimeinterface.constants.iso8601
     * @throws TypeError – $pubDate parameter is not a string
     * @throws Exception – unable to parse $pubDate date string
     * @param  DateTime|string $pubDate
     */
    public function setPubDate(mixed $pubDate): self {
        $this->pubDate = ($pubDate instanceof DateTime) ? $pubDate : new DateTime($pubDate);

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getDescription(): ?string {
        return $this->description;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setDescription(string $description): self {
        $this->description = $description;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getDuration(): ?int {
        return $this->duration;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setDuration(int $duration): self {
        $this->duration = $duration;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getWebsite(): ?string {
        return $this->website;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setWebsite(string $website): self {
        $this->website = $website;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getImageUrl(): ?string {
        return $this->imageUrl;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setImageUrl(string $imageUrl): self {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function isExplicit(): bool {
        return $this->isExplicit;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setExplicit(bool $value): self {
        $this->isExplicit = $value;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function shouldBeRemoved(): bool {
        return $this->shouldBeRemoved;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setShouldBeRemoved(bool $value): self {
        $this->shouldBeRemoved = $value;

        return $this;
    }
}