<?php declare(strict_types=1);

namespace ElevenLabs\Api\Decoder;

/**
 * Interface DecoderInterface.
 */
interface DecoderInterface
{
    /**
     * Decode a string into an object or array of objects.
     *
     * @param string $data
     * @param string $format
     *
     * @return \stdClass|\stdClass[]
     */
    public function decode(string $data, string $format);
}
