<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests;

use ElevenLabs\Api\Definition\RequestDefinition;
use ElevenLabs\Api\Definition\RequestDefinitions;
use ElevenLabs\Api\Schema;
use PHPUnit\Framework\TestCase;

/**
 * Class SchemaTest.
 */
class SchemaTest extends TestCase
{
    /** @test */
    public function itCanIterateAvailableOperations()
    {
        $request = $this->prophesize(RequestDefinition::class);
        $request->getMethod()->willReturn('GET');
        $request->getPathTemplate()->willReturn('/api/pets/{id}');
        $request->getOperationId()->willReturn('getPet');

        $requests = new RequestDefinitions([$request->reveal()]);

        $schema = new Schema($requests);

        $operations = $schema->getRequestDefinitions();

        $this->assertTrue(is_iterable($operations));

        foreach ($operations as $operationId => $operation) {
            $this->assertSame('getPet', $operationId);
        }
    }

    /** @test */
    public function itCanResolveAnOperationIdFromAPathAndMethod()
    {
        $request = $this->prophesize(RequestDefinition::class);
        $request->getMethod()->willReturn('GET');
        $request->getPathTemplate()->willReturn('/api/pets/{id}');
        $request->getOperationId()->willReturn('getPet');

        $requests = $this->prophesize(RequestDefinitions::class);
        $requests->getIterator()->willReturn(new \ArrayIterator([$request->reveal()]));

        $schema = new Schema($requests->reveal());

        $operationId = $schema->findOperationId('GET', '/api/pets/1234');

        $this->assertSame('getPet', $operationId);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     *
     * @expectedExceptionMessage Unable to resolve the operationId for path /api/pets/1234
     */
    public function itThrowAnExceptionWhenNoOperationIdCanBeResolved()
    {
        $requests = new RequestDefinitions();

        $schema = new Schema($requests, '/api');
        $schema->findOperationId('GET', '/api/pets/1234');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     *
     * @expectedExceptionMessage Unable to resolve the operationId for path /api
     */
    public function itThrowAnExceptionWhenNoOperationIdCanBeResolved2()
    {
        $request = $this->prophesize(RequestDefinition::class);
        $request->getMethod()->willReturn('GET');
        $request->getPathTemplate()->willReturn('/api/pets/{id}');

        $requests = $this->prophesize(RequestDefinitions::class);
        $requests->getIterator()->willReturn(new \ArrayIterator([$request->reveal()]));

        $schema = new Schema($requests->reveal());

        $schema->findOperationId('GET', '/api');
    }

    /** @test */
    public function itProvideARequestDefinition()
    {
        $request = $this->prophesize(RequestDefinition::class);
        $request->getMethod()->willReturn('GET');
        $request->getPathTemplate()->willReturn('/pets/{id}');
        $request->getOperationId()->willReturn('getPet');

        $requests = new RequestDefinitions([$request->reveal()]);

        $schema = new Schema($requests, '/api');
        $actual = $schema->getRequestDefinition('getPet');

        $this->assertEquals($request->reveal(), $actual);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     *
     * @expectedExceptionMessage Unable to find request definition for operationId getPet
     */
    public function itThrowAnExceptionWhenNoRequestDefinitionIsFound()
    {
        $requests = new RequestDefinitions();

        $schema = new Schema($requests, '/api');
        $schema->getRequestDefinition('getPet');
    }

    /** @test */
    public function itCanBeSerialized()
    {
        $requests = new RequestDefinitions();

        $schema = new Schema($requests);
        $serialized = serialize($schema);

        $this->assertEquals($schema, unserialize($serialized));
    }
}
