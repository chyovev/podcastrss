<?php

namespace PodcastRSS;

class Podcast
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
     * When a podcast's feed gets moved to a new URL
     * altogether, said address should be specified
     * in this property.
     * The old address should be redirected to it
     * using a 301 (moved permanently) redirect.
     * 
     * @var string 
     */
    protected ?string $newFeedUrl = null;


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
    public function getDescription(): ?string {
        return $this->description;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setDescription(string $description): self {
        $this->description = $description;

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
    public function getLanguage(): ?string {
        return $this->language;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setLanguage(string $language): self {
        $this->language = $language;

        return $this;
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
    public function setExplicit(bool $value): self {
        $this->isExplicit = $value;

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
    public function addEpisode(Episode $episode): self {
        $this->episodes[] = $episode;

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
    public function getAuthor(): ?string {
        return $this->author;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setAuthor(string $author): self {
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
        $this->contactName = $contactName;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getContactEmail(): ?string {
        return $this->contactEmail;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setContactEmail(string $contactEmail): self {
        $this->contactEmail = $contactEmail;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getCopyright(): ?string {
        return $this->copyright;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setCopyright(string $copyright): self {
        $this->copyright = $copyright;
        
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

    ///////////////////////////////////////////////////////////////////////////
    public function getNewFeedUrl(): ?string {
        return $this->newFeedUrl;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setNewFeedUrl(string $newFeedUrl): self {
        $this->newFeedUrl = $newFeedUrl;
        
        return $this;
    }

}