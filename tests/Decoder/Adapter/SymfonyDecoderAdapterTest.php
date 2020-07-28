<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests\Decoder\Adapter;

use ElevenLabs\Api\Decoder\Adapter\SymfonyDecoderAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * Class SymfonyDecoderAdapterTest.
 */
class SymfonyDecoderAdapterTest extends TestCase
{
    /** @test */
    public function itShouldTransformDecodedXmlIntoAnArrayOfObject()
    {
        $data = '<response><item key="0"><foo>foo1</foo></item><item key="1"><foo>foo2</foo></item></response>';
        $decoder = new SymfonyDecoderAdapter(new XmlEncoder());
        $actual = $decoder->decode($data, 'xml');

        $this->assertTrue(is_array($actual));
        $this->assertCount(2, $actual);

        $this->assertInstanceOf(\stdClass::class, $actual[0]);
        $this->assertSame(0, $actual[0]->{'@key'});
        $this->assertSame('foo1', $actual[0]->foo);

        $this->assertInstanceOf(\stdClass::class, $actual[1]);
        $this->assertSame(1, $actual[1]->{'@key'});
        $this->assertSame('foo2', $actual[1]->foo);
    }

    /** @test */
    public function itShouldDecodeAJsonStringIntoAnArrayOfObject()
    {
        $data = '[{"foo": "foo1"}, {"foo": "foo2"}]';

        $decoder = new SymfonyDecoderAdapter(new JsonDecode(true));
        $actual = $decoder->decode($data, 'json');

        $this->assertTrue(is_array($actual));
        $this->assertCount(2, $actual);

        $this->assertInstanceOf(\stdClass::class, $actual[0]);
        $this->assertSame('foo1', $actual[0]->foo);

        $this->assertInstanceOf(\stdClass::class, $actual[1]);
        $this->assertSame('foo2', $actual[1]->foo);
    }
}
