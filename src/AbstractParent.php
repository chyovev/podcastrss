<?php

namespace PodcastRSS;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

abstract class AbstractParent implements XmlSerializable {

    /**
     * Instead of passing around the writer each time
     * a property needs to be serialized, the xmlSerialize()
     * method sets it as a property.
     * 
     * @var Writer $writer
     */
    protected Writer $xmlWriter;


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
        $this->xmlWriter = $writer;

        $this->convertToXml();
    }


    ///////////////////////////////////////////////////////////////////////////
    /**
     * The converToXml() method is replacing the xmlSerialize()
     * method but without the need to pass the Writer as a parameter.
     */
    abstract protected function convertToXml(): void;


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

}