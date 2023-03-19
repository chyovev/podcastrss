<?php

namespace PodcastRSS;

use InvalidArgumentException;
use PodcastRSS\Enum\PodcastType;
use Sabre\Xml\Service;
use Sabre\Xml\Writer;

/**
 * Required elements:
 *     - title
 *     - description
 *     - imageUrl
 *     - language
 *     - categories (at least one)
 *     - episodes (at least one)
 */

class Podcast extends AbstractParent
{

    /**
     * The podcast title, required.
     * 
     * @var string
     */
    protected ?string $title = null;

    /**
     * One or more sentences describing the podcast, required.
     * Maximum amount of text allowed is 4000 bytes (~3600 chars).
     * Rich text formatting supported (<p>, <ol>, <ul>, <li>, <a>).
     * Optional for Google, but still recommended.
     * Required by Apple.
     * 
     * @var string
     */
    protected ?string $description = null;

    /**
     * Artwork of the podcast, required.
     * JPEG or PNG, 72 dpi, RGB colorspace. 
     * Min size: 1400x1400 px, max size: 3000x3000 px,
     * 
     * @var string
     */
    protected ?string $imageUrl = null;

    /**
     * The language spoken on the podcast, required.
     * Must be a valid ISO 639 item (two-letter language codes,
     * with some possible modifiers, such as "en-us").
     * 
     * @var string
     */
    protected ?string $language = null;

    /**
     * Category (and sub-category, if applicable) of the podcast, required.
     * Although multiple categories and subcategories are supported,
     * Apple Podcasts recognizes only the first pair.
     * 
     * Structure (see addCategory() method):
     *     ['Main Category' => ['Subcategory1', 'Subcategory2']]
     * 
     * @see https://podcasters.apple.com/support/1691-apple-podcasts-categories
     * @var string[]
     */
    protected array $categories = [];

    /**
     * The podcast parental advisory information,
     * i.e. whether explicit content is present.
     * Required element, default value is false.
     * 
     * @var bool
     */
    protected bool $isExplicit = false;

    /**
     * All of the podcast's episodes,
     * at least one element is required.
     * 
     * @var Episode[]
     */
    protected array $episodes = [];

    /**
     * Website associated with the podcast
     * (not the URL of the feed itself).
     * Optional for Apple, but still recommended.
     * Required by Google.
     * 
     * @var string
     */
    protected ?string $website = null;

    /**
     * Name of person or group responsible
     * for creating the podcast.
     * Similar to $owner, but visible.
     * Not required, but recommended.
     * 
     * @var string
     */
    protected ?string $author = null;

    /**
     * Podcast owner contact information (email and name).
     * Not publically displayed, but used for administrative
     * communication (at least with Apple Podcasts).
     * Not required, but recommended.
     * It makes sense to extract both these properties into a
     * single class, but this might overcomplicate things.
     * See the setContact() method.
     * 
     * @var string
     */
    protected ?string $contactName  = null;
    protected ?string $contactEmail = null;
    
    /**
     * The show copyright details.
     * Not required.
     * 
     * @var string
     */
    protected ?string $copyright = null;

    /**
     * Whether the podcast should be removed from
     * the platforms, default value is false.
     * Not required.
     * 
     * @var bool
     */
    protected bool $shouldBeRemoved = false;

    /**
     * NB! Used by Apple Podcasts only.
     * 
     * When a podcast is marked as archived,
     * no new episodes will ever be published.
     * The podcast will remain visible, though.
     * To hide it altogether, use the
     * $shouldBeRemoved property.
     * Default value for $isArchived is false.
     * 
     * @var bool
     */
    protected bool $isArchived = false;

    /**
     * When a podcast's feed gets moved to a new URL
     * altogether, said address should be specified
     * in this property.
     * The old address should be redirected to it
     * using a 301 (moved permanently) redirect.
     * 
     * @var string 
     */
    protected ?string $newFeedUrl = null;

    /**
     * NB! Used by Apple Podcasts only.
     * 
     * There are two types of podcasts: Episodic and Serial.
     * The property gets populated using the respective
     * factory methods.
     * 
     * @see self :: newEpisodic()
     * @see self :: newSerial()
     * @var string
     */
    protected ?string $type = null;


    ///////////////////////////////////////////////////////////////////////////
    /**
     * Episodic podcasts are intended to be consumed without any
     * specific order. Apple Podcasts will present newest episodes
     * first and display the publish date (required) of each episode.
     * If organized into seasons, the newest season will be presented
     * first – otherwise, episodes will be grouped by year published,
     * newest first.
     */
    public static function newEpisodic() {
        return new self(PodcastType::EPISODIC);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Serial podcasts are intended to be consumed in sequential order.
     * Apple Podcasts will present the oldest episodes first and display
     * the episode numbers (required) of each episode.
     * If organized into seasons, the newest season will be presented first
     * and $episodeNumber must be given for each episode.
     */
    public static function newSerial() {
        return new self(PodcastType::SERIAL);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Since the constructor's visibility is set to protected,
     * a Podcast object can only be initialized using one of
     * the two factory methods calling the constructor.
     * Alternatively, potential extensions of the Podcast class
     * can bypass this behavior by declaring a public constructor.
     * 
     * @param string $type – type of podcast
     */
    protected function __construct(string $type) {
        $this->setType($type);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getType(): ?string {
        return $this->type;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setType(string $type): self {
        $this->validateIsOneOf($type, PodcastType::getValidValues());
        
        $this->type = $type;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Check whether the podcast's type is 'Serial'.
     * The method is used for the validation of new episodes
     * being added to the set.
     * 
     * @see self :: validateNewEpisode()
     * @return bool
     */
    public function isTypeSerial(): bool {
        return $this->type === PodcastType::SERIAL;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getTitle(): ?string {
        return $this->title;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setTitle(string $title): self {
        $this->validateMaxLength($title);

        $this->title = $title;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getDescription(): ?string {
        return $this->description;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setDescription(string $description): self {
        $this->validateMaxLengthHTML($description);

        $this->description = $description;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getImageUrl(): ?string {
        return $this->imageUrl;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setImageUrl(string $imageUrl): self {
        $this->validateUrl($imageUrl);
        
        $this->imageUrl = $imageUrl;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getLanguage(): ?string {
        return $this->language;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – invalid value
     */
    public function setLanguage(string $language): self {
        $this->validateLanguage($language);

        $this->language = $language;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Having an enum type class with all available languages would
     * be tedious, so a regex validation will be used instead.
     * The language should be ISO 639-1 compatible, i.e. two lowercase
     * letters, potentially followed by a dash and two uppercase letters.
     * 
     * @throws InvalidArgumentException – invalid value
     */
    protected function validateLanguage(string $language): void {
        $pattern = '/^[a-z]{2}(-[A-Z]{2})?$/';

        if ( ! preg_match($pattern, $language)) {
            throw new InvalidArgumentException("The provided language '{$language}' is not ISO 639-1 compatible");
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getCategories(): array {
        return $this->categories;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function addCategory(string $category, array $subcategories = []): self {
        $this->categories[$category] = $subcategories;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function isExplicit(): bool {
        return $this->isExplicit;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function markAsExplicit(): self {
        return $this->setExplicit(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setExplicit(bool $value): self {
        $this->isExplicit = $value;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getExplicitValue(): string {
        return var_export($this->isExplicit(), true);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @return Episode[]
     */
    public function getEpisodes(): array {
        return $this->episodes;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * A mass setter for all episodes at once.
     * 
     * @param Episode[] $episodes
     */
    public function setEpisodes(array $episodes): self {
        $this->episodes = [];

        foreach ($episodes as $item) {
            $this->addEpisode($item);
        }

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function addEpisode(Episode $episode): self {
        $this->validateNewEpisode($episode);

        $this->episodes[] = $episode;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Each episode being added to the set must undergo
     * a proper validation first.
     * 
     * @throws InvalidArgumentException – failed validation
     * @param  Episode $episode – episode being added to set
     * @return void
     */
    protected function validateNewEpisode(Episode $episode): void {
        if ($this->isTypeSerial()) {
            $this->validateEpisodeHasNumber($episode);
        }

        $this->validateEpisodeNumberIsUnique($episode);
        $this->validateEpisodeGuidIsUnique($episode);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * All episodes of a Serial podcast should have an episode number.
     * 
     * @throws InvalidArgumentException – missing episode number
     * @param  Episode $episode – episode being added to set
     * @return void
     */
    protected function validateEpisodeHasNumber(Episode $episode): void {
        if ( ! $episode->getEpisodeNumber()) {
            throw new InvalidArgumentException("Required episode number is missing");
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure the episode number of the episode being added to
     * the set is not used by another episode already in the set.
     * 
     * @throws InvalidArgumentException – episode number conflict
     * @param  Episode $episode – episode being added to set
     * @return void
     */
    protected function validateEpisodeNumberIsUnique(Episode $episode): void {
        $newNumber = $episode->getEpisodeNumber();

        foreach ($this->episodes as $item) {
            $number = $item->getEpisodeNumber();

            // since episode numbers are optional, make sure
            // there is a value before checking it for uniqueness
            if ($number && $newNumber === $number) {
                throw new InvalidArgumentException("Cannot add episode as the episode number '{$newNumber}' is already being used");
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure the guid of the episode being added to the
     * set is not used by another episode already in the set.
     * 
     * @throws InvalidArgumentException – guid conflict
     * @param  Episode $episode – episode being added to set
     * @return void
     */
    protected function validateEpisodeGuidIsUnique(Episode $episode): void {
        $newGuid = $episode->getGuid();

        foreach ($this->episodes as $item) {
            $guid = $item->getGuid();

            if ($newGuid === $guid) {
                throw new InvalidArgumentException("Cannot add episode as the guid '{$newGuid}' is already being used");
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getWebsite(): ?string {
        return $this->website;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setWebsite(string $website): self {
        $this->validateUrl($website);

        $this->website = $website;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getAuthor(): ?string {
        return $this->author;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setAuthor(string $author): self {
        $this->validateMaxLength($author);

        $this->author = $author;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * A single method shortcut to set both contact properties.
     */
    public function setContact(string $name, string $email): self {
        $this->setContactName($name);
        $this->setContactEmail($email);

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getContactName(): ?string {
        return $this->contactName;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setContactName(string $contactName): self {
        $this->validateMaxLength($contactName);

        $this->contactName = $contactName;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getContactEmail(): ?string {
        return $this->contactEmail;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setContactEmail(string $contactEmail): self {
        $this->validateEmail($contactEmail);
        $this->validateMaxLength($contactEmail);

        $this->contactEmail = $contactEmail;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getCopyright(): ?string {
        return $this->copyright;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setCopyright(string $copyright): self {
        $this->validateMaxLength($copyright);

        $this->copyright = $copyright;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function shouldBeRemoved(): bool {
        return $this->shouldBeRemoved;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Keep in mind that it may take some time for a podcast marked
     * for removal to actually be removed from the platforms it is on.
     */
    public function markForRemoval(): self {
        return $this->setShouldBeRemoved(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setShouldBeRemoved(bool $value): self {
        $this->shouldBeRemoved = $value;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function isArchived(): bool {
        return $this->isArchived;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function markAsArchived(): self {
        return $this->setArchived(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setArchived(bool $value): self {
        $this->isArchived = $value;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getNewFeedUrl(): ?string {
        return $this->newFeedUrl;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setNewFeedUrl(string $newFeedUrl): self {
        $this->validateUrl($newFeedUrl);

        $this->newFeedUrl = $newFeedUrl;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Generate an RSS XML using Sabre's XML library.
     * 
     * @return string
     */
    public function generateRss(): string {
        $service = new Service();

        $service->namespaceMap = [
            'http://www.itunes.com/dtds/podcast-1.0.dtd' => 'itunes',
            'http://purl.org/rss/1.0/modules/content/'   => 'content',
        ];

        // to add an attribute to the root element, a callback
        // function should be used for the service $value attribute
        return $service->write('rss', function(Writer $writer) {
            $writer->writeAttribute('version','2.0');
            $writer->write(['channel' => $this]);
        });
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
        $this->validateHasValue('title',       $this->title);
        $this->validateHasValue('description', $this->description);
        $this->validateHasValue('imageUrl',    $this->imageUrl);
        $this->validateHasValue('language',    $this->language);

        $this->validateArrayMinSize('categories', $this->categories, 1);
        $this->validateArrayMinSize('episodes',   $this->episodes,   1);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Serialize a Podcast object to an RSS XML string.
     */
    protected function convertToXml(): void {
        $this->serializeTitle();
        $this->serializeWebsite();
        $this->setializeLanguage();
        $this->serializeAuthor();
        $this->serializeCopyright();
        $this->serializeDescription();
        $this->serializeType();
        $this->serializeContact();
        $this->serializeImageUrl();
        $this->serializeCategories();
        $this->serializeExplicit();
        $this->serializeNewFeedUrl();
        $this->serializeShouldBeRemoved();
        $this->serializeArchived();
        $this->serializeEpisodes();
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeTitle(): void {
        $this->writeToXml('title', $this->title);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeWebsite(): void {
        $this->writeToXml('link', $this->website);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function setializeLanguage(): void {
        $this->writeToXml('language', $this->language);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeAuthor(): void {
        $this->writeToXml('itunes:author', $this->author);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeCopyright(): void {
        $this->writeToXml('copyright', $this->copyright);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeDescription(): void {
        $this->writeToXml('description', $this->description);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeType(): void {
        $this->writeToXml('itunes:type', $this->type);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeContact(): void {
        $data = [
            'itunes:name'  => $this->contactName,
            'itunes:email' => $this->contactEmail,
        ];

        $this->writeToXml('itunes:owner', $data);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeImageUrl(): void {
        $this->writeToXml('itunes:image', $this->imageUrl);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeCategories(): void {
        $data = [];

        foreach ($this->categories as $mainCategory => $subcategories) {
            $data[] = $this->getCategorySerializationData($mainCategory, $subcategories);
        }

        $this->xmlWriter->write($data);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Serialized subcategories are subelements of serialized
     * main categories, but they have the same structure:
     * 
     *     <category text="(sub)category" />
     * 
     * Therefore, the subcategories serialization is carried
     * out by simply calling this same method recursively.
     * 
     * @param  string $mainCategory
     * @param  array  $subcategories
     * @return array
     */
    protected function getCategorySerializationData(string $mainCategory, array $subcategories = []): array {
        $data = [
            'name'       => 'category',
            'attributes' => ['text' => trim($mainCategory)],
        ];

        foreach ($subcategories as $item) {
            $data['value'][] = $this->getCategorySerializationData($item);
        }

        return $data;
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeExplicit(): void {
        $data = $this->getExplicitValue();

        $this->writeToXml('itunes:explicit', $data);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeNewFeedUrl(): void {
        $this->writeToXml('itunes:new-feed-url', $this->newFeedUrl);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeShouldBeRemoved(): void {
        $this->writeToXml('itunes:block', 'Yes');
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeArchived(): void {
        $this->writeToXml('itunes:complete', 'Yes');
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * The episode array consists of Episode objects
     * which also implement the xmlSerialize() method,
     * so the value for Item will have the Episode's
     * serialized content.
     */
    protected function serializeEpisodes(): void {
        foreach ($this->episodes as $episode) {
            $this->writeToXml('Item', $episode);
        }
    }

}