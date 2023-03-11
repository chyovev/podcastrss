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

}