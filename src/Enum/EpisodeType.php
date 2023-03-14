<?php

namespace PodcastRSS\Enum;

use PodcastRSS\Interfaces\Enum;

final class EpisodeType implements Enum
{

    /**
     * All possible episode types according
     * to Apple Podcasts.
     * 
     * @see https://help.apple.com/itc/podcasts_connect/#/itcb54353390
     * @var string
     */ 
    const FULL    = 'Full',
          TRAILER = 'Trailer',
          BONUS   = 'Bonus';


    ///////////////////////////////////////////////////////////////////////////
    public static function getValidValues(): array {
        return [
            self::FULL,
            self::TRAILER,
            self::BONUS,
        ];
    }
}
