<?php

namespace PodcastRSS\Enum;

use PodcastRSS\Interfaces\Enum;

/**
 * The MimeType enum class is used to validate
 * the Episode MIME type.
 */

final class MimeType implements Enum
{

    /**
     * All possible episode mime types according
     * to Apple Podcasts.
     * Each extension corresponds with a MIME type.
     * 
     * @see \PodcastRSS\Enum\ExtensionType
     * @see https://help.apple.com/itc/podcasts_connect/#/itcb54353390
     * @var string
     */ 
    const AUDIO_X_M4A     = 'audio/x-m4a',
          AUDIO_MPEG      = 'audio/mpeg',
          VIDEO_QUICKTIME = 'video/quicktime',
          VIDEO_MP4       = 'video/mp4',
          VIDEO_X_M4V     = 'video/x-m4v',
          APPLICATION_PDF = 'application/pdf';


    ///////////////////////////////////////////////////////////////////////////
    public static function getValidValues(): array {
        return [
            self::AUDIO_X_M4A,
            self::AUDIO_MPEG,
            self::VIDEO_QUICKTIME,
            self::VIDEO_MP4,
            self::VIDEO_X_M4V,
            self::APPLICATION_PDF,
        ];
    }
}