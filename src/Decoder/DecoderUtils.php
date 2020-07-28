<?php declare(strict_types=1);

namespace ElevenLabs\Api\Decoder;

/**
 * Class DecoderUtils.
 */
class DecoderUtils
{
    /**
     * @param string $contentType
     *
     * @return string
     */
    public static function extractFormatFromContentType(string $contentType): string
    {
        $parts = explode('/', $contentType);
        $format = array_pop($parts);

        if (false !== $pos = strpos($format, ';')) {
            $format = substr($format, 0, $pos);
        }

        return $format;
    }
}
