<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests\Definition;

use ElevenLabs\Api\Definition\Parameters;
use ElevenLabs\Api\Definition\ResponseDefinition;
use PHPUnit\Framework\TestCase;

/**
 * Class ResponseDefinitionTest.
 */
class ResponseDefinitionTest extends TestCase
{
    /** @test */
    public function itCanBeSerialized()
    {
        $responseDefinition = new ResponseDefinition(200, ['application/json'], new Parameters([]));
        $serialized = serialize($responseDefinition);

        $this->assertEquals($responseDefinition, unserialize($serialized));
    }
}
