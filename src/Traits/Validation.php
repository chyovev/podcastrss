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
     * @param  int $maxLength
     * @return void
     */
    protected function validateMaxLengthHTML(string $html, int $maxLength): void {
        $string = trim(strip_tags($html));
        $length = mb_strlen($string, 'utf-8');

        if ($length > $maxLength) {
            throw new InvalidArgumentException("Passed HTML string surpasses the maximum length of {$maxLength} characters.");
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

}