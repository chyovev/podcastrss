<?php

namespace PodcastRSS;

use DOMDocument;
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
 *     - website (required by Google)
 */

class Podcast extends AbstractParent
{

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
     *     [
     *         0 => 'Main Category',
     *         1 => ['Subcategory1', 'Subcategory2'],
     *     ]
     * 
     * @see https://podcasters.apple.com/support/1691-apple-podcasts-categories
     * @var array
     */
    protected array $categories = [];

    /**
     * All of the podcast's episodes,
     * at least one element is required.
     * 
     * @var Episode[]
     */
    protected array $episodes = [];

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
        return (new self())->setTypeEpisodic();
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
        return (new self())->setTypeSerial();
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getType(): ?string {
        return $this->type;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setTypeEpisodic(): self {
        return $this->setType(PodcastType::EPISODIC);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setTypeSerial(): self {
        return $this->setType(PodcastType::SERIAL);
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
    /**
     * The categories parameter consists of string subarrays
     * which should be passed to the addCategory() method as
     * a variable-length argument list.
     * 
     * @throws InvalidArgumentException – missing main category (no/empty parameter)
     * @param  string[] $categories
     * @return self
     */
    public function setCategories(array $categories): self {
        $this->categories = [];

        foreach ($categories as $subcategories) {
            $this->addCategory(...$subcategories);
        }

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Add a category with a list of subcategories (if any).
     * The first argument is always the main category,
     * while all consecutive parameters are considered
     * subcategories of said main category.
     * 
     * @throws InvalidArgumentException – missing main category (no/empty parameter)
     * @param string[] $categories
     */
    public function addCategory(string ...$categories): self {
        $this->filterEmptyValues($categories);

        $mainCategory = array_shift($categories);

        if (is_null($mainCategory)) {
            throw new InvalidArgumentException('Method addCategory expects at least one main category.');
        }

        $this->categories[] = [$mainCategory, $categories];

        return $this;
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
    public function isArchived(): bool {
        return $this->isArchived;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function markAsArchived(): self {
        return $this->setIsArchived(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setIsArchived(bool $value): self {
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
            self::ITUNES_NS  => 'itunes',
            self::CONTENT_NS => 'content',
        ];

        // to add an attribute to the root element, a callback
        // function should be used for the service $value attribute
        $xml = $service->write('rss', function(Writer $writer) {
            $writer->writeAttribute('version','2.0');
            $writer->write(['channel' => $this]);
        });

        return $this->addEncodingToXml($xml);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * By default the XML generated by Sabre's serialization library
     * has no encoding specified in the opening tag, but as it's
     * recommended to have it, it can be added subsequently
     * by passing the XML to a DOMDocument object.
     * 
     * @param  string $xml – XML generated by Sabre's library
     * @return string XML with UTF-8 encoding
     */
    private function addEncodingToXml(string $xml): string {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        // loading the XML overwrites the encoding specified
        // in the constructor, so it should be readded afterwards
        $dom->encoding = 'UTF-8';

        return $dom->saveXML();
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
        $this->validateHasValue('website',     $this->website);

        $this->validateArrayMinSize('categories', $this->categories, 1);
        $this->validateArrayMinSize('episodes',   $this->episodes,   1);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Serialize a Podcast object to an RSS XML string.
     * Elements with empty values will be automatically stripped.
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
    protected function setializeLanguage(): void {
        $this->writeToXml('language', $this->language);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeAuthor(): void {
        $tagName = $this->getItunesElementName('author');
        
        $this->writeToXml($tagName, $this->author);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeCopyright(): void {
        $this->writeToXml('copyright', $this->copyright);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeType(): void {
        $tagName = $this->getItunesElementName('type');

        $this->writeToXml($tagName, $this->type);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeContact(): void {
        $tagName = $this->getItunesElementName('owner');
        $data    = [
            $this->getItunesElementName('name')  => $this->contactName,
            $this->getItunesElementName('email') => $this->contactEmail,
        ];

        $this->writeToXml($tagName, $data);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeCategories(): void {
        $data = [];

        // each categories element is an array: the first element
        // is considered a main category, and the second: subcategories
        foreach ($this->categories as $item) {
            $data[] = $this->getCategorySerializationData($item[0], $item[1]);
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
        $tagName    = 'category';
        $attributes = ['text' => trim($mainCategory)];
        $value      = [];

        foreach ($subcategories as $item) {
            $value[] = $this->getCategorySerializationData($item);
        }

        return $this->prepareXmlRecord($tagName, $value, $attributes);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeNewFeedUrl(): void {
        $tagName = $this->getItunesElementName('new-feed-url');

        $this->writeToXml($tagName, $this->newFeedUrl);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeArchived(): void {
        if ($this->isArchived()) {
            $tagName = $this->getItunesElementName('complete');

            $this->writeToXml($tagName, 'Yes');
        }
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