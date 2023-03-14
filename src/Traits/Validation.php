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
    public function validateIsOneOf(mixed $value, array $validValues): void {
        if ( ! in_array($value, $validValues, true)) {
            $valid = implode(', ', $validValues);

            throw new InvalidArgumentException("Invalid value '{$value}', expected one of: {$valid}");
        }
    }

}