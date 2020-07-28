<?php declare(strict_types=1);

namespace ElevenLabs\Api\Decoder\Adapter;

use ElevenLabs\Api\Decoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface as SymfonyDecoderInterface;

/**
 * Class SymfonyDecoderAdapter.
 */
class SymfonyDecoderAdapter implements DecoderInterface
{
    /**
     * @var SymfonyDecoderInterface
     */
    private $decoder;

    /**
     * SymfonyDecoderAdapter constructor.
     *
     * @param SymfonyDecoderInterface $decoder
     */
    public function __construct(SymfonyDecoderInterface $decoder)
    {
        $this->decoder = $decoder;
    }

    /**
     * @param string $data
     * @param string $format
     *
     * @return mixed
     */
    public function decode(string $data, string $format)
    {
        $context = [];

        if (preg_match('#json#', $format)) {
            // the JSON schema validator need an object hierarchy
            $context['json_decode_associative'] = false;
        }

        $decoded = $this->decoder->decode($data, $format, $context);

        if ('xml' === $format) {
            // the JSON schema validator need an object hierarchy
            $decoded = json_decode(json_encode($decoded));
        }

        return $decoded;
    }
}
