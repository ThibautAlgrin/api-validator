<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests\Decoder;

use ElevenLabs\Api\Decoder\DecoderUtils;
use PHPUnit\Framework\TestCase;

/**
 * Class DecoderUtilsTest.
 */
class DecoderUtilsTest extends TestCase
{
    /**
     * @dataProvider dataForExtractFormatFromContentType
     *
     * @param string $contentType
     * @param string $format
     */
    public function testExtractFormatFromContentType($contentType, $format)
    {
        $this->assertSame($format, DecoderUtils::extractFormatFromContentType($contentType));
    }

    /**
     * @return array
     */
    public function dataForExtractFormatFromContentType()
    {
        return [
            ['text/plain', 'plain'],
            ['application/xhtml+xml', 'xhtml+xml'],
            ['application/hal+json; charset=utf-8', 'hal+json'],
        ];
    }
}
