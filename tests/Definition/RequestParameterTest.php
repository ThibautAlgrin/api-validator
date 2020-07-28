<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests\Definition;

use ElevenLabs\Api\Definition\Parameter;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestParameterTest.
 */
class RequestParameterTest extends TestCase
{
    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     *
     * @expectedExceptionMessage nowhere is not a supported parameter location, supported: path, header, query, body, formData
     */
    public function itThrowAnExceptionOnUnsupportedParameterLocation()
    {
        new Parameter('nowhere', 'foo');
    }

    /** @test */
    public function itShouldUseDefaultValue()
    {
        $requestParameter = new Parameter('query', 'foo');

        $this->assertFalse($requestParameter->hasSchema());
        $this->assertFalse($requestParameter->isRequired());
    }

    /** @test */
    public function itCanBeSerialized()
    {
        $requestParameter = new Parameter('query', 'foo', true, new \stdClass());
        $serialized = serialize($requestParameter);

        $this->assertEquals($requestParameter, unserialize($serialized));
        $this->assertTrue($requestParameter->hasSchema());
        $this->assertTrue($requestParameter->isRequired());
    }
}
