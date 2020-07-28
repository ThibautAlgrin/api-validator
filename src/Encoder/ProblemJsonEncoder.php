<?php declare(strict_types=1);

namespace ElevenLabs\Api\Encoder;

use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Class ProblemJsonEncoder.
 */
class ProblemJsonEncoder extends JsonEncoder
{
    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        return parent::decode($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return 'problem+json' === $format;
    }
}
