<?php

namespace PodcastRSS;

use DateTime;
use Exception;
use InvalidArgumentException;
use TypeError;
use PodcastRSS\Enum\EpisodeType;
use PodcastRSS\Enum\ExtensionType;
use PodcastRSS\Enum\MimeType;

/**
 * Required elements:
 *     - title
 *     - fileSize
 *     - mimeType
 *     - episodeUrl
 */

class Episode extends AbstractParent
{

    /**
     * Filesize of the episode (in bytes), required.
     * 
     * @var int
     */
    protected ?int $fileSize = null;
    
    /**
     * MIME type of the episode's file, required.
     * 
     * @see \PodcastRSS\Enum\MimeType – all supported mime types
     * @var string
     */
    protected ?string $mimeType = null;

    /**
     * Url of the episode, required.
     * 
     * @see \PodcastRSS\Enum\ExtensionType – all supported file extensions
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
     * Duration of the episode (in seconds).
     * Not required, but recommended.
     * 
     * @var int
     */
    protected ?int $duration = null;

    /**
     * NB! Used by Apple Podcasts only.
     * 
     * Episode numbers are optional for Episodic podcasts,
     * but are mandatory for Serial podcasts.
     * Value should be a non-zero integer.
     * 
     * @var int
     */
    protected ?int $episodeNumber = null;

    /**
     * NB! Used by Apple Podcasts only.
     * 
     * The episode season number (if applicable).
     * Value should be a non-zero integer.
     * If there's only one season, the season
     * number will remain hidden.
     * 
     * @var int
     */
    protected ?int $seasonNumber = null;

    /**
     * NB! Used by Apple Podcasts only.
     * 
     * There are three types of episodes:
     * Full, Trailer and Bonus.
     * The property gets populated using
     * the respective factory methods.
     * 
     * @see self :: newFull()
     * @see self :: newTrailer()
     * @see self :: newBonus()
     * 
     * @var string
     */
    protected ?string $type = null;


    ///////////////////////////////////////////////////////////////////////////
    /**
     * Initialize a regular (full) episode. This is the most common type.
     */
    public static function newFull() {
        return (new self())->setTypeFull();
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Trailer type should be used for short, promotional pieces
     * of content that represent a preview of the podcast. 
     */
    public static function newTrailer() {
        return (new self())->setTypeTrailer();
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Extra content (such as behind the scenes or promotional
     * content for another show) should be marked as Bonus type.
     */
    public static function newBonus() {
        return (new self())->setTypeBonus();
    }


    ///////////////////////////////////////////////////////////////////////////
    public function getType(): ?string {
        return $this->type;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setTypeFull(): self {
        return $this->setType(EpisodeType::FULL);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setTypeTrailer(): self {
        return $this->setType(EpisodeType::TRAILER);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setTypeBonus(): self {
        return $this->setType(EpisodeType::BONUS);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setType(string $type): self {
        $this->validateIsOneOf($type, EpisodeType::getValidValues());
        
        $this->type = $type;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Initialize an episode from a file in order to
     * populate the fileSize and mimeType properties.
     * 
     * @param  string $filePath – path to file on local disk
     * @return self
     */
    public static function fromFile(string $filePath): self {
        return (new self())->setFromFile($filePath);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Each episode must have a file URL, size and MIME type.
     * Each of these properties can be set individually via
     * the respective setters, but this helper method can
     * be also used to parse a *local* file in order to extract
     * the file size and MIME type.
     * 
     * @param  string $filePath – path to file on local disk
     * @return self
     */
    public function setFromFile(string $filePath): self {
        $fileSize = @filesize($filePath);
        $mimeType = @mime_content_type($filePath);

        return $this
            ->setFileSize($fileSize)
            ->setMimeType($mimeType);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getFileSize(): ?int {
        return $this->fileSize;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setFileSize(int $fileSize): self {
        $this->validateIsPositive($fileSize);
        
        $this->fileSize = $fileSize;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getMimeType(): ?string {
        return $this->mimeType;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setMimeType(string $mimeType): self {
        $this->validateIsOneOf($mimeType, MimeType::getValidValues());

        $this->mimeType = $mimeType;

        // once the MIME type gets set, validate it
        // against the episode URL in case it's also set
        $this->validateMimeTypeAgainstEpisodeUrl();

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Once the MIME type and the episode URL are both set,
     * validate them against each other.
     * This method is invoked by the setters of both properties.
     * 
     * @throws InvalidArgumentException – URL vs. MIME type mismatch
     * @return void
     */
    protected function validateMimeTypeAgainstEpisodeUrl(): void {
        $episodeUrl = $this->getEpisodeUrl();
        $mimeType   = $this->getMimeType();

        // if one of the properties is still not set, abort validation
        if (is_null($episodeUrl) || is_null($mimeType)) {
            return;
        }

        $extension = $this->getEpisodeUrlExtension($episodeUrl);

        $this->validateMimeTypeAndUrlExtensionCorrespondence($mimeType, $extension);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * There's a total of 6 MIME types supported which correspond
     * to 6 file extensions (the extension gets extracted from the
     * episode URL). If the two do not match, an exception gets thrown.
     * 
     * @throws InvalidArgumentException – URL extension does not match MIME type
     * @param  string $mimeType
     * @param  string $extension
     * @return void
     */
    protected function validateMimeTypeAndUrlExtensionCorrespondence(string $mimeType, string $extension): void {
        $mapping = [
            ExtensionType::M4A => MimeType::AUDIO_X_M4A,
            ExtensionType::MP3 => MimeType::AUDIO_MPEG,
            ExtensionType::MOV => MimeType::VIDEO_QUICKTIME,
            ExtensionType::MP4 => MimeType::VIDEO_MP4,
            ExtensionType::M4V => MimeType::VIDEO_X_M4V,
            ExtensionType::PDF => MimeType::APPLICATION_PDF,
        ];

        $expectedMimeType = $mapping[$extension];

        if ($expectedMimeType !== $mimeType) {
            throw new InvalidArgumentException("Episode URL extension '{$extension}' does not correspond with MIME type {$mimeType}; expected {$expectedMimeType} instead");
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getEpisodeUrl(): ?string {
        return $this->episodeUrl;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setEpisodeUrl(string $episodeUrl): self {
        $this->validateUrl($episodeUrl);
        $this->validateEpisodeUrlExtension($episodeUrl);

        $this->episodeUrl = $episodeUrl;

        // once the episode URL gets set, validate it
        // against the episode MIME type in case it's also set
        $this->validateMimeTypeAgainstEpisodeUrl();

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure the extension used in the episode URL is a valid one.
     * 
     * @throws InvalidArgumentException – invalid extension
     * @param  string $episodeUrl
     * @return void
     */
    protected function validateEpisodeUrlExtension(string $episodeUrl): void {
        $extension = $this->getEpisodeUrlExtension($episodeUrl);

        $this->validateIsOneOf($extension, ExtensionType::getValidValues());
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function getEpisodeUrlExtension(string $episodeUrl): string {
        $extension = pathinfo($episodeUrl, PATHINFO_EXTENSION);

        return mb_strtolower($extension, 'utf-8');
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getGuid(): ?string {
        return $this->guid;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setGuid(string $guid): self {
        $this->validateMaxLength($guid);

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
    public function getDuration(): ?int {
        return $this->duration;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setDuration(int $duration): self {
        $this->validateIsPositive($duration);

        $this->duration = $duration;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getEpisodeNumber(): ?int {
        return $this->episodeNumber;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setEpisodeNumber(int $episodeNumber): self {
        $this->validateIsPositive($episodeNumber);

        $this->episodeNumber = $episodeNumber;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getSeasonNumber(): ?int {
        return $this->seasonNumber;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setSeasonNumber(int $seasonNumber): self {
        $this->validateIsPositive($seasonNumber);

        $this->seasonNumber = $seasonNumber;

        return $this;
    }


    /* ===================================================================== */
    /*                       XML SERIALIZATION METHODS                       */
    /* ===================================================================== */

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure that all required data is set before serializing it to XML.
     * 
     * @throws InvalidArgumentException
     */
    public function validateDataIntegrity(): void {
        $this->validateHasValue('title',      $this->title);
        $this->validateHasValue('fileSize',   $this->fileSize);
        $this->validateHasValue('mimeType',   $this->mimeType);
        $this->validateHasValue('episodeUrl', $this->episodeUrl);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Serialize an Episode object to an RSS XML string.
     * Elements with empty values will be automatically stripped.
     */
    protected function convertToXml(): void {
        $this->serializeTitle();
        $this->serializeDescription();
        $this->serializeFile();
        $this->serializeGuid();
        $this->serializePubDate();
        $this->serializeDuration();
        $this->serializeWebsite();
        $this->serializeImageUrl();
        $this->serializeExplicit();
        $this->serializeEpisodeNumber();
        $this->serializeSeasonNumber();
        $this->serializeEpisodeType();
        $this->serializeShouldBeRemoved();
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * The file element is a combination of 3 properties:
     *     - file size (in bytes)
     *     - MIME type
     *     - episode public URL
     */
    protected function serializeFile(): void {
        $attributes = [
            'length' => $this->getFileSize(),
            'type'   => $this->getMimeType(),
            'url'    => $this->getEpisodeUrl(),
        ];

        $this->writeToXml('enclosure', NULL, $attributes);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeGuid(): void {
        $this->writeToXml('guid', $this->guid);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializePubDate(): void {
        $date = $this->getPubDate();

        if ($date) {
            $this->writeToXml('pubDate', $date->format('r'));
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeDuration(): void {
        $this->writeToXml('duration', $this->duration);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeEpisodeNumber(): void {
        $tagName = $this->getItunesElementName('episode');

        $this->writeToXml($tagName, $this->episodeNumber);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeSeasonNumber(): void {
        $tagName = $this->getItunesElementName('season');

        $this->writeToXml($tagName, $this->seasonNumber);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeEpisodeType(): void {
        $tagName = $this->getItunesElementName('episodeType');

        $this->writeToXml($tagName, $this->type);
    }

}