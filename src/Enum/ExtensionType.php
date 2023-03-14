<?php

namespace PodcastRSS\Enum;

use PodcastRSS\Interfaces\Enum;

/**
 * The ExtensionType enum class is used to validate
 * the Episode URL.
 */

final class ExtensionType implements Enum
{

    /**
     * All possible episode file extensions according
     * to Apple Podcasts.
     * Each extension corresponds with a MIME type.
     * 
     * @see \PodcastRSS\Enum\MimeType
     * @see https://help.apple.com/itc/podcasts_connect/#/itcb54353390
     * @var string
     */ 
    const M4A = 'm4a',
          MP3 = 'mp3',
          MOV = 'mov',
          MP4 = 'mp4',
          M4V = 'm4v',
          PDF = 'pdf';


    ///////////////////////////////////////////////////////////////////////////
    public static function getValidValues(): array {
        return [
            self::M4A,
            self::MP3,
            self::MOV,
            self::MP4,
            self::M4V,
            self::PDF,
        ];
    }
}