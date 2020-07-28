<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests\Definition;

use ElevenLabs\Api\Definition\Parameter;
use ElevenLabs\Api\Definition\Parameters;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestParametersTest.
 */
class RequestParametersTest extends TestCase
{
    /** @test */
    public function itCanBeTraversed()
    {
        $requestParameter = $this->prophesize(Parameter::class);
        $requestParameter->getLocation()->willReturn('query');
        $requestParameter->getName()->willReturn('foo');

        $requestParameters = new Parameters([$requestParameter->reveal()]);

        $this->assertInstanceOf(\Traversable::class, $requestParameters);
        $values = [];
        foreach ($requestParameters->getIterator() as $value) {
            $values[] = $value;
        }
        assertThat($requestParameters, containsOnlyInstancesOf(Parameter::class));
        $this->assertNotEmpty($values);
    }

    /** @test */
    public function itCanBeSerialized()
    {
        $requestParameters = new Parameters([]);
        $serialized = serialize($requestParameters);

        $this->assertEquals($requestParameters, unserialize($serialized));
    }

    /** @test */
    public function itCanResolveARequestParameterByName()
    {
        $requestParameter = $this->prophesize(Parameter::class);
        $requestParameter->getLocation()->willReturn('query');
        $requestParameter->getName()->willReturn('foo');

        $requestParameters = new Parameters([$requestParameter->reveal()]);

        $this->assertSame($requestParameter->reveal(), $requestParameters->getByName('foo'));
        $this->assertNull($requestParameters->getByName('bar'));
    }
}
