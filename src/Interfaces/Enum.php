<?php

namespace PodcastRSS\Interfaces;

/**
 * The Enum interface must be implemented
 * by all Enum type classes.
 */

interface Enum {

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Get all valid values for an Enum type.
     * Used for validation in setter methods.
     * 
     * @see \PodcastRSS\Traits\Validation :: validateIsOneOf()
     */
    public static function getValidValues(): array;

}