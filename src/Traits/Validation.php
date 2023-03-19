<?php

namespace PodcastRSS\Traits;

use InvalidArgumentException;

/**
 * Common validation methods shared between
 * the Podcast and Episode classes.
 */

trait Validation
{

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure the passed value is among the valid values array.
     * 
     * @throws InvalidArgumentException – invalid value
     * @param  mixed $value
     * @param  array $validValues
     * @return void
     */
    protected function validateIsOneOf(mixed $value, array $validValues): void {
        if ( ! in_array($value, $validValues, true)) {
            $valid = implode(', ', $validValues);

            throw new InvalidArgumentException("Invalid value '{$value}', expected one of: {$valid}");
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure the passed HTML does not surpass the maximum length
     * after all tags have been stripped.
     * 
     * @throws InvalidArgumentException – HTML string too long
     * @param  string $html
     * @param  int $maxLength (3600 characters default)
     * @return void
     */
    protected function validateMaxLengthHTML(string $html, int $maxLength = 3600): void {
        $string = strip_tags($html);
        
        $this->validateMaxLength($string, $maxLength);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure the passed string does not surpass the maximum length.
     * 
     * @throws InvalidArgumentException – string too long
     * @param  string $string
     * @param  int $maxLength (255 characters default)
     * @return void
     */
    protected function validateMaxLength(string $string, int $maxLength = 255): void {
        $length = mb_strlen(trim($string), 'utf-8');

        if ($length > $maxLength) {
            throw new InvalidArgumentException("Passed string surpasses the maximum length of {$maxLength} characters.");
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure that the passed string is a valid email address.
     * 
     * @throws InvalidArgumentException – email is not valid
     * @param  string $email
     * @return void
     */
    protected function validateEmail(string $email): void {
        if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("'{$email}' is not a valid e-mail address");
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure that the passed string is a valid URL.
     * 
     * @throws InvalidArgumentException – URL is not valid
     * @param  string $url
     * @return void
     */
    protected function validateUrl(string $url): void {
        if ( ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("'{$url}' is not a valid URL string");
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure that the passed number is a positive integer.
     * 
     * @throws InvalidArgumentException – number is not positive
     * @param  int $number
     * @return void
     */
    protected function validateIsPositive(int $number): void {
        if ($number <= 0) {
            throw new InvalidArgumentException("Expected a positive integer, got '{$number}' instead");
        }
    }


    /* ===================================================================== */
    /*                       XML SERIALIZATION METHODS                       */
    /* ===================================================================== */

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure a property has a non-null, non-empty value.
     * 
     * @throws InvalidArgumentException – missing value
     * @param  string $properyName – name of the property, used for the exception
     * @param  mixed  $value
     * @return void
     */
    protected function validateHasValue(string $propertyName, mixed $value): void {
        $isString = is_string($value);

        // if value is an empty string, or not a string, but still equal to false
        if (($isString && trim($value) === '') || ( ! $isString && ! $value)) {
            throw new InvalidArgumentException("Missing value for {$propertyName}, cannot serialize.");
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Make sure an array has at least N elements.
     * 
     * @throws InvalidArgumentException – validation failed
     * @param  string $properyName – name of the property, used for the exception
     * @param  array  $array
     * @param  int    $minSize
     * @return void
     */
    protected function validateArrayMinSize(string $propertyName, array $array, int $minSize): void {
        $size = count($array);

        if ($size < $minSize) {
            throw new InvalidArgumentException("Expected at least {$minSize} elements for {$propertyName}, got {$size} instead.");
        }
    }

}