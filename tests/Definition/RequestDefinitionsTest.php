<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests\Definition;

use ElevenLabs\Api\Definition\RequestDefinition;
use ElevenLabs\Api\Definition\RequestDefinitions;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestDefinitionsTest.
 */
class RequestDefinitionsTest extends TestCase
{
    /** @test */
    public function itCanBeSerialized()
    {
        $requestDefinition = new RequestDefinitions([]);
        $serialized = serialize($requestDefinition);

        $this->assertEquals($requestDefinition, unserialize($serialized));
    }

    /** @test */
    public function itProvideARequestDefinition()
    {
        $requestDefinition = $this->prophesize(RequestDefinition::class);
        $requestDefinition->getOperationId()->willReturn('getFoo');

        $requestDefinitions = new RequestDefinitions([$requestDefinition->reveal()]);

        $this->assertInstanceOf(RequestDefinition::class, $requestDefinitions->getRequestDefinition('getFoo'));

        $definitions = [];
        foreach ($requestDefinitions as $requestDefinition) {
            $definitions[] = $requestDefinition;
        }
        $this->assertNotEmpty($definitions);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     *
     * @expectedExceptionMessage Unable to find request definition for operationId getFoo
     */
    public function itThrowAnExceptionWhenNoRequestDefinitionIsFound()
    {
        $requestDefinitions = new RequestDefinitions([]);
        $requestDefinitions->getRequestDefinition('getFoo');
    }
}
