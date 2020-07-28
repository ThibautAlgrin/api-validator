<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests\Definition;

use ElevenLabs\Api\Definition\Parameters;
use ElevenLabs\Api\Definition\RequestDefinition;
use ElevenLabs\Api\Definition\ResponseDefinition;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestDefinitionTest.
 */
class RequestDefinitionTest extends TestCase
{
    /** @test */
    public function itCanBeSerialized()
    {
        $requestDefinition = new RequestDefinition(
            'GET',
            'getFoo',
            '/foo/{id}',
            new Parameters([]),
            ['application/json'],
            []
        );

        $serialized = serialize($requestDefinition);

        $this->assertEquals($requestDefinition, unserialize($serialized));
        $this->assertFalse($requestDefinition->hasBodySchema());
        $this->assertNull($requestDefinition->getPathSchema());
        $this->assertFalse($requestDefinition->hasQueryParametersSchema());
        $this->assertNull($requestDefinition->getQueryParametersSchema());
        $this->assertFalse($requestDefinition->hasHeadersSchema());
        $this->assertNull($requestDefinition->getHeadersSchema());
    }

    /** @test */
    public function itProvideAResponseDefinition()
    {
        $responseDefinition = $this->prophesize(ResponseDefinition::class);
        $responseDefinition->getStatusCode()->willReturn(200);

        $requestDefinition = new RequestDefinition(
            'GET',
            'getFoo',
            '/foo/{id}',
            new Parameters([]),
            ['application/json'],
            [$responseDefinition->reveal()]
        );
        $this->assertFalse($requestDefinition->hasBodySchema());
        $this->assertFalse($requestDefinition->hasQueryParametersSchema());
        $this->assertFalse($requestDefinition->hasPathSchema());
        $this->assertFalse($requestDefinition->hasHeadersSchema());

        $this->assertInstanceOf(ResponseDefinition::class, $requestDefinition->getResponseDefinition(200));
    }

    /** @test */
    public function itProvideAResponseDefinitionUsingDefaultValue()
    {
        $statusCodes = [200, 'default'];
        $responseDefinitions = [];
        foreach ($statusCodes as $statusCode) {
            $responseDefinition = $this->prophesize(ResponseDefinition::class);
            $responseDefinition->getStatusCode()->willReturn($statusCode);
            $responseDefinitions[] = $responseDefinition->reveal();
        }

        $requestDefinition = new RequestDefinition(
            'GET',
            'getFoo',
            '/foo/{id}',
            new Parameters([]),
            ['application/json'],
            $responseDefinitions
        );
        $this->assertFalse($requestDefinition->hasBodySchema());
        $this->assertFalse($requestDefinition->hasQueryParametersSchema());
        $this->assertFalse($requestDefinition->hasPathSchema());
        $this->assertFalse($requestDefinition->hasHeadersSchema());

        $this->assertInstanceOf(ResponseDefinition::class, $requestDefinition->getResponseDefinition(500));
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     *
     * @expectedExceptionMessage No response definition for GET /foo/{id} is available for status code 200
     */
    public function itThrowAnExceptionWhenNoResponseDefinitionIsFound()
    {
        $requestDefinition = new RequestDefinition(
            'GET',
            'getFoo',
            '/foo/{id}',
            new Parameters([]),
            ['application/json'],
            []
        );
        $this->assertFalse($requestDefinition->hasBodySchema());
        $this->assertFalse($requestDefinition->hasQueryParametersSchema());
        $this->assertFalse($requestDefinition->hasPathSchema());
        $this->assertFalse($requestDefinition->hasHeadersSchema());

        $requestDefinition->getResponseDefinition(200);
    }
}
