<?php

namespace PodcastRSS;

use InvalidArgumentException;
use PodcastRSS\Traits\Validation;
use Sabre\Xml\Element\Cdata;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

abstract class AbstractParent implements XmlSerializable {

    use Validation;

    /**
     * All namespaces utilized in the serialized XML.
     * Apart from being used in the namespace map
     * delacaration, they're also required for the
     * so called Clark-notation.
     * 
     * @see https://sabre.io/xml/clark-notation/
     * @var string
     */
    const ITUNES_NS  = 'http://www.itunes.com/dtds/podcast-1.0.dtd',
          CONTENT_NS = 'http://purl.org/rss/1.0/modules/content/';

    /**
     * Title of the podcast/episode, required.
     * The episode number and/or season should NOT be
     * included in the title; instead, the respective
     * properties should be used: $episodeNumber and
     * $seasonNumber.
     * 
     * @var string
     */
    protected ?string $title = null;

    /**
     * One or more sentences describing the podcast/episode.
     * Maximum amount of text allowed is 4000 bytes (~3600 chars).
     * Rich text formatting supported (<p>, <ol>, <ul>, <li>, <a>).
     * If the description contains HTML tags,
     * use setDescriptionHtml() instead of regular setter.
     * Optional, but actually recommended.
     * 
     * @var string
     */
    protected ?string $description = null;

    /**
     * When the description has HTML tags, it should be
     * wrapped in a CDATA tag during XML serialization.
     * Set either together with the description via the
     * setDescriptionHMTL() method, or individually
     * by its setter: markDescriptionAsHtml().
     * 
     * @var bool
     */
    protected bool $isDescriptionHtml = false;

    /**
     * Artwork of the podcast/episode.
     * JPEG or PNG, 72 dpi, RGB colorspace. 
     * Min size: 1400x1400 px, max size: 3000x3000 px.
     * Required for Podcasts, optional for Episodes.
     * 
     * @var string
     */
    protected ?string $imageUrl = null;

    /**
     * The podcast/episode parental advisory information,
     * i.e. whether explicit content is present.
     * Required element, default value is false.
     * 
     * @var bool
     */
    protected bool $isExplicit = false;

    /**
     * Website associated with the podcast/episode
     * (not the podcast feed or episode file URL).
     * Optional for Apple, but still recommended.
     * Required by Google for Podcasts.
     * 
     * @var string
     */
    protected ?string $website = null;

    /**
     * Whether the podcast/episode should be removed from
     * the platforms, default value is false.
     * Not required.
     * 
     * @var bool
     */
    protected bool $shouldBeRemoved = false;

    /**
     * Instead of passing around the writer each time
     * a property needs to be serialized, the xmlSerialize()
     * method sets it as a property.
     * 
     * @var Writer $writer
     */
    protected Writer $xmlWriter;


    ///////////////////////////////////////////////////////////////////////////
    public function getTitle(): ?string {
        return $this->title;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setTitle(string $title): static {
        $this->validateMaxLength($title);

        $this->title = $title;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getDescription(): ?string {
        return $this->description;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Descriptions containing HTML tags should be set using
     * this method, otherwise they will be escaped during
     * XML serialization.
     */
    public function setDescriptionHtml(string $description): static {
        return $this
            ->setDescription($description)
            ->markDescriptionAsHtml();
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setDescription(string $description): static {
        $this->validateMaxLengthHTML($description);

        $this->description = $description;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function isDescriptionHtml(): bool {
        return $this->isDescriptionHtml;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function markDescriptionAsHtml(): static {
        return $this->setIsDescriptionHtml(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setIsDescriptionHtml(bool $value): static {
        $this->isDescriptionHtml = $value;

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
    public function setImageUrl(string $imageUrl): static {
        $this->validateUrl($imageUrl);
        
        $this->imageUrl = $imageUrl;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function isExplicit(): bool {
        return $this->isExplicit;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function markAsExplicit(): static {
        return $this->setIsExplicit(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setIsExplicit(bool $value): static {
        $this->isExplicit = $value;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getExplicitValue(): string {
        return var_export($this->isExplicit(), true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getWebsite(): ?string {
        return $this->website;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @throws InvalidArgumentException – failed validation
     */
    public function setWebsite(string $website): static {
        $this->validateUrl($website);

        $this->website = $website;
        
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
    public function markForRemoval(): static {
        return $this->setShouldBeRemoved(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setShouldBeRemoved(bool $value): static {
        $this->shouldBeRemoved = $value;

        return $this;
    }


    /* ===================================================================== */
    /*                       XML SERIALIZATION METHODS                       */
    /* ===================================================================== */

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Sabre's XML serialization library works only if all classes
     * subject to serialization implement the xmlSerialize() method.
     * However, this covers just the most basic use-case scenario:
     * adding new elements to an XML document.
     * The Podcast & Episode classes need some "special treatment",
     * though: required data must be checked for integrity, while
     * optional data should be serialized only if there's a value.
     * Therefore, some changes are introduced as opposed to the
     * default library's behavior: the children classes need to
     * implement two methods instead – data integrity validation
     * and convertToXml() which does not need the Writer as a param,
     * as it's already set in the class property.
     * 
     * @throws InvalidArgumentException – missing data
     * @param  Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer): void {
        $this->validateDataIntegrity();

        $this->xmlWriter = $writer;

        $this->convertToXml();
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure that all required data is set.
     * If not, throw an exception.
     * 
     * @throws InvalidArgumentException – missing data
     * @return void
     */
    abstract public function validateDataIntegrity(): void;


    ///////////////////////////////////////////////////////////////////////////
    /**
     * The converToXml() method is replacing the xmlSerialize()
     * method but without the need to pass the Writer as a parameter.
     */
    abstract protected function convertToXml(): void;


    ///////////////////////////////////////////////////////////////////////////
    protected function serializeTitle(): void {
        $this->writeToXml('title', $this->title);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If the description was previously marked as HTML,
     * it should be passed to the approparite serialization
     * method which guarantees that HTML tags will be preserved.
     */
    protected function serializeDescription(): void {
        $this->isDescriptionHtml
            ? $this->writeHtmlToXml('description', $this->description)
            : $this->writeToXml('description',     $this->description);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeImageUrl(): void {
        $tagName = $this->getItunesElementName('image');
        
        $this->writeToXml($tagName, $this->imageUrl);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeExplicit(): void {
        $tagName = $this->getItunesElementName('explicit');
        $data    = $this->getExplicitValue();

        $this->writeToXml($tagName, $data);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeWebsite(): void {
        $this->writeToXml('link', $this->website);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function serializeShouldBeRemoved(): void {
        if ($this->shouldBeRemoved()) {
            $tagName = $this->getItunesElementName('block');

            $this->writeToXml($tagName, 'Yes');
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * A shortcut element to add an HTML element to the XML being generated.
     * HTML elements must be wrapped in CDATA, otherwise all tags will
     * be escaped, i.e. converted to their ASCII representations,
     * e.g. > would become &gt;
     * 
     * @param string $tagName
     * @param mixed  $value
     * @param array  $attributes
     */
    protected function writeHtmlToXml(string $tagName, mixed $value, array $attributes = []): void {
        if ($value) {
            $value = new Cdata($value);
        }

        $this->writeToXml($tagName, $value, $attributes);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * A shortcut method to add an element to the XML being generated.
     * Since some elements have just attributes, while others only
     * values, both these parameters are optional.
     * However, if both are missing, no empty tag should not be generated.
     * 
     * @param string $tagName
     * @param mixed  $value
     * @param array  $attributes
     */
    protected function writeToXml(string $tagName, mixed $value, array $attributes = []): void {
        // cast all attributes to string since the underlying
        // XMLWriter::writeAttribute throws an exception if the
        // value is not a string (e.g. integer $fileSize of Episode)
        $attributes = array_map('strval', $attributes);

        $this->filterEmptyValues($attributes);

        if (is_array($value)) {
            $this->filterEmptyValues($value);
        }
        
        if ( ! ($value || $attributes)) {
            return;
        }

        $this->xmlWriter->write([
            'name'       => $tagName,
            'attributes' => $attributes,
            'value'      => $value,
        ]);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Before serializing attributes or array values for tags,
     * make sure all empty values are stripped.
     * 
     * @param array &$data – passed by reference
     */
    protected function filterEmptyValues(array &$data): void {
        foreach ($data as $key => &$item) {
            if (is_null($item)) {
                unset($data[$key]);
            }

            // subarrays should call current method recursively
            elseif (is_array($item)) {
                $this->filterEmptyValues($item);
            }

            // if the value is a string, trim it
            // and in case it's an empty string – remove it
            elseif (is_string($item)) {
                $item = trim($item);

                if ($item === '') {
                    unset($data[$key]);
                }
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Add the itunes namespace as a prefix in front of the element name.
     * 
     * @param  string $localName
     * @return string
     */
    protected function getItunesElementName(string $localName): string {
        return $this->getElementNameForNamespace($localName, self::ITUNES_NS);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Some XML elements are specific to a certain namespace and should
     * have that namespace as a prefix, e.g. <itunes:block>.
     * Although possible to simply provide it as a concatenated string,
     * Sabre's advise is to use the namespace's URL as a prefix surrounded
     * by curly brackets, and the library will take care of the mapping
     * (granted a namespace mapping is provided for the Service object).
     * 
     * @see https://sabre.io/xml/clark-notation/
     * @param string $localName
     * @param string $namespace
     * @return string
     */
    protected function getElementNameForNamespace(string $localName, string $namespace): string {
        // surround namespace by curly brackets
        $namespace = '{' . trim($namespace, '{}') . '}';

        return "{$namespace}{$localName}";
    }

}