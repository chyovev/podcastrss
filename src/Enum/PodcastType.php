<?php

namespace PodcastRSS\Enum;

use PodcastRSS\Interfaces\Enum;

final class PodcastType implements Enum
{

    /**
     * All possible podcast types according
     * to Apple Podcasts.
     * 
     * @see https://help.apple.com/itc/podcasts_connect/#/itcb54353390
     * @var string
     */ 
    const EPISODIC = 'Episodic',
          SERIAL   = 'Serial';

          
    ///////////////////////////////////////////////////////////////////////////
    public static function getValidValues(): array {
        return [
            self::EPISODIC,
            self::SERIAL,
        ];
    }
}
