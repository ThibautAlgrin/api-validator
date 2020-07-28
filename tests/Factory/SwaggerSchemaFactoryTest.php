<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests\Factory;

use ElevenLabs\Api\Definition\RequestDefinition;
use ElevenLabs\Api\Definition\Parameter;
use ElevenLabs\Api\Definition\Parameters;
use ElevenLabs\Api\Definition\ResponseDefinition;
use ElevenLabs\Api\Factory\SwaggerSchemaFactory;
use ElevenLabs\Api\Schema;
use ElevenLabs\Api\Tests\Definition\RequestParametersTest;
use PHPUnit\Framework\TestCase;

/**
 * Class SwaggerSchemaFactoryTest.
 */
class SwaggerSchemaFactoryTest extends TestCase
{
    /** @test */
    public function itCanCreateASchemaFromAJsonFile()
    {
        $schema = $this->getPetStoreSchemaJson();

        $this->assertInstanceOf(Schema::class, $schema);
        $this->assertSame('/v2', $schema->getBasePath());
        $this->assertSame('petstore.swagger.io', $schema->getHost());
        $this->assertSame(['https', 'http'], $schema->getSchemes());

        $requestDefinition = $schema->getRequestDefinitions()->getRequestDefinition('addPet');

        $this->assertSame('POST', $requestDefinition->getMethod());
        $this->assertSame(['application/json', 'application/xml'], $requestDefinition->getContentTypes());
        $this->assertTrue($requestDefinition->hasHeadersSchema());
        $this->assertSame(
            '{"type":"object","required":[],"properties":{"api_key":{"type":"string"}}}',
            json_encode($requestDefinition->getHeadersSchema())
        );
        $this->assertFalse($requestDefinition->hasPathSchema());
        $this->assertTrue($requestDefinition->hasBodySchema());
        $this->assertFalse($requestDefinition->hasQueryParametersSchema());
        $this->assertSame('addPet', $requestDefinition->getOperationId());
        $this->assertSame('/v2/pet', $requestDefinition->getPathTemplate());
        $this->assertInstanceOf(Parameters::class, $requestDefinition->getRequestParameters());
        $this->assertSame(['application/json', 'application/xml'], $requestDefinition->getContentTypes());
        $bodySchema = $requestDefinition->getBodySchema();
        $this->assertFalse(isset($bodySchema->{'$ref'}));
    }

    /** @test */
    public function itCanCreateASchemaFromAYamlFile()
    {
        $schema = $this->getPetStoreSchemaYaml();

        $this->assertInstanceOf(Schema::class, $schema);
        $this->assertSame('/v2', $schema->getBasePath());
        $this->assertSame('petstore.swagger.io', $schema->getHost());
        $this->assertSame(['https', 'http'], $schema->getSchemes());

        $requestDefinition = $schema->getRequestDefinitions()->getRequestDefinition('addPet');
        $this->assertSame('POST', $requestDefinition->getMethod());
        $this->assertSame('addPet', $requestDefinition->getOperationId());
        $this->assertSame('/v2/pet', $requestDefinition->getPathTemplate());
        $this->assertInstanceOf(Parameters::class, $requestDefinition->getRequestParameters());
        $this->assertSame(['application/json', 'application/xml'], $requestDefinition->getContentTypes());
        $bodySchema = $requestDefinition->getBodySchema();
        $this->assertFalse(isset($bodySchema->{'$ref'}));
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     *
     * @expectedExceptionMessageRegExp /does not provide a supported extension/
     */
    public function itThrowAnExceptionWhenTheSchemaFileIsNotSupported()
    {
        $unsupportedFile = 'file://'.dirname(__DIR__).'/fixtures/petstore.txt';

        (new SwaggerSchemaFactory())->createSchema($unsupportedFile);
    }

    /** @test */
    public function itShouldHaveSchemaProperties()
    {
        $schema = $this->getPetStoreSchemaJson();

        $this->assertSame('petstore.swagger.io', $schema->getHost());
        $this->assertSame('/v2', $schema->getBasePath());
        $this->assertSame(['https', 'http'], $schema->getSchemes());
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     *
     * @expectedExceptionMessage You need to provide an operationId for GET /something
     */
    public function itThrowAnExceptionWhenAnOperationDoesNotProvideAnId()
    {
        $this->getSchemaFromFile('operation-without-an-id.json');
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     *
     * @expectedExceptionMessage You need to specify at least one response for GET /something
     */
    public function itThrowAnExceptionWhenAnOperationDoesNotProvideResponses()
    {
        $this->getSchemaFromFile('operation-without-responses.json');
    }

    /** @test */
    public function itSupportAnOperationWithoutParameters()
    {
        $schema = $this->getSchemaFromFile('operation-without-parameters.json');
        $definition = $schema->getRequestDefinition('getSomething');

        $this->assertFalse($definition->hasHeadersSchema());
        $this->assertFalse($definition->hasBodySchema());
        $this->assertFalse($definition->hasQueryParametersSchema());
    }

    /** @test */
    public function itCanCreateARequestDefinition()
    {
        $schema = $this->getPetStoreSchemaJson();

        $requestDefinition = $schema->getRequestDefinition('findPetsByStatus');

        $this->assertInstanceOf(RequestDefinition::class, $requestDefinition);
        $this->assertSame('GET', $requestDefinition->getMethod());
        $this->assertSame('findPetsByStatus', $requestDefinition->getOperationId());
        $this->assertSame('/v2/pet/findByStatus', $requestDefinition->getPathTemplate());
        $this->assertSame([], $requestDefinition->getContentTypes());
        $this->assertInstanceOf(Parameters::class, $requestDefinition->getRequestParameters());

        $responseDefinition = $requestDefinition->getResponseDefinition(200);
        $this->assertInstanceOf(ResponseDefinition::class, $responseDefinition);
        $this->assertSame(200, $responseDefinition->getStatusCode());
        $this->assertSame(['application/xml', 'application/json'], $responseDefinition->getContentTypes());
        $this->assertInstanceOf(Parameter::class, $responseDefinition->getParameters()->getBody());

        $body = $responseDefinition->getParameters()->getBody();
        $this->assertSame('body', $body->getLocation());
        $this->assertSame('body', $body->getName());
        $this->assertTrue($body->isRequired());
        $this->assertInstanceOf(\stdClass::class, $body->getSchema());
        $this->assertSame('array', $body->getSchema()->type);

        $this->assertInstanceOf(ResponseDefinition::class, $requestDefinition->getResponseDefinition(400));
    }

    /** @test */
    public function itCanCreateARequestBodyParameter()
    {
        $schema = $this->getPetStoreSchemaJson();

        $requestParameters = $schema->getRequestDefinition('addPet')->getRequestParameters();

        $this->assertInstanceOf(Parameters::class, $requestParameters);
        $this->assertInstanceOf(Parameter::class, $requestParameters->getBody());
        $this->assertTrue($requestParameters->hasBodySchema());
        $this->assertTrue(is_object($requestParameters->getBodySchema()));
    }

    /** @test */
    public function itCanCreateRequestPath()
    {
        $schema = $this->getPetStoreSchemaJson();

        $requestParameters = $schema->getRequestDefinition('getPetById')->getRequestParameters();

        assertThat($requestParameters->getPath(), containsOnlyInstancesOf(Parameter::class));
    }

    /** @test */
    public function itCanCreateRequestQueryParameters()
    {
        $schema = $this->getPetStoreSchemaJson();

        $requestParameters = $schema->getRequestDefinition('findPetsByStatus')->getRequestParameters();

        assertThat($requestParameters->getQuery(), containsOnlyInstancesOf(Parameter::class));
        $this->assertTrue(is_object($requestParameters->getQueryParametersSchema()));
        $queryParametersSchema = $requestParameters->getQueryParametersSchema();
        $this->assertTrue(isset($queryParametersSchema->properties->status));
        $this->assertTrue(is_object($queryParametersSchema->properties->status));
    }

    /** @test */
    public function itCanCreateRequestHeadersParameter()
    {
        $schema = $this->getPetStoreSchemaJson();

        $requestParameters = $schema->getRequestDefinition('deletePet')->getRequestParameters();

        assertThat($requestParameters->getHeaders(), containsOnlyInstancesOf(Parameter::class));
        $this->assertTrue($requestParameters->hasHeadersSchema());
        $this->assertTrue(is_object($requestParameters->getHeadersSchema()));

        $headers = $requestParameters->getHeaders();
        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('api_key', $headers);

        $apiKey = $headers['api_key'];
        $this->assertSame('header', $apiKey->getLocation());
        $this->assertSame('api_key', $apiKey->getName());
        $this->assertFalse($apiKey->isRequired());
        $this->assertNotNull($apiKey->getSchema());
    }

    /** @test */
    public function itCanCreateAResponseDefinition()
    {
        $schema = $this->getPetStoreSchemaJson();

        $responseDefinition = $schema->getRequestDefinition('getPetById')->getResponseDefinition(200);

        $this->assertInstanceOf(ResponseDefinition::class, $responseDefinition);
        $this->assertFalse($responseDefinition->hasHeadersSchema());
        $this->assertTrue($responseDefinition->hasBodySchema());
        $this->assertTrue(is_object($responseDefinition->getBodySchema()));
        $this->assertSame(200, $responseDefinition->getStatusCode());
        $this->assertContains('application/json', $responseDefinition->getContentTypes());
    }

    /**
     * @test
     */
    public function itCanCreateAResponseDefinitionWithHeaders()
    {
        $schema = $this->getPetStoreSchemaYaml();

        $responseDefinition = $schema->getRequestDefinition('loginUser')->getResponseDefinition(200);

        $this->assertInstanceOf(ResponseDefinition::class, $responseDefinition);
        $this->assertTrue($responseDefinition->hasHeadersSchema());
        $this->assertTrue($responseDefinition->hasBodySchema());
        $this->assertTrue(is_object($responseDefinition->getBodySchema()));
        $this->assertSame(200, $responseDefinition->getStatusCode());
        $this->assertContains('application/json', $responseDefinition->getContentTypes());
        $this->assertTrue($responseDefinition->hasHeadersSchema());

        $headerSchema = '{"type":"object","required":["X-Rate-Limit","X-Expires-After"],"properties":{"X-Rate-Limit":{"type":"integer","format":"int32","description":"calls per hour allowed by the user"},"X-Expires-After":{"type":"string","format":"date-time","description":"date in UTC when token expires"}}}';

        $this->assertSame($headerSchema, json_encode($responseDefinition->getHeadersSchema()));
    }

    public function itUseTheSchemaDefaultConsumesPropertyWhenNotProvidedByAnOperation()
    {
        $schema = $this->getSchemaFromFile('schema-with-default-consumes-and-produces-properties.json');
        $definition = $schema->getRequestDefinition('postSomething');

        assertThat($definition->getContentTypes(), contains('application/json'));
    }

    /** @test */
    public function itUseTheSchemaDefaultProducesPropertyWhenNotProvidedByAnOperationResponse()
    {
        $schema = $this->getSchemaFromFile('schema-with-default-consumes-and-produces-properties.json');
        $responseDefinition = $schema
            ->getRequestDefinition('postSomething')
            ->getResponseDefinition(201);

        assertThat($responseDefinition->getContentTypes(), contains('application/json'));
    }

    /**
     * @test
     * @dataProvider getGuessableContentTypes
     */
    public function itGuessTheContentTypeFromRequestParameters($operationId, $expectedContentType)
    {
        $schema = $this->getSchemaFromFile('request-without-content-types.json');

        $definition = $schema->getRequestDefinition($operationId);

        assertThat($definition->getContentTypes(), contains($expectedContentType));
    }

    public function getGuessableContentTypes()
    {
        return [
            'body' => [
                'operationId' => 'postBodyWithoutAContentType',
                'contentType' => 'application/json',
            ],
            'formData' => [
                'operationId' => 'postFromDataWithoutAContentType',
                'contentType' => 'application/x-www-form-urlencoded',
            ],
        ];
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     *
     * @expectedExceptionMessage Parameters cannot have body and formData locations at the same time in /post/with-conflicting-locations
     */
    public function itFailWhenTryingToGuessTheContentTypeFromARequestWithMultipleBodyLocations()
    {
        $schemaFile = 'file://'.dirname(__DIR__).'/fixtures/request-with-conflicting-locations.json';
        (new SwaggerSchemaFactory())->createSchema($schemaFile);
    }

    /**
     * @return Schema
     */
    private function getPetStoreSchemaJson()
    {
        return $this->getSchemaFromFile('petstore.json');
    }

    /**
     * @return Schema
     */
    private function getPetStoreSchemaYaml()
    {
        return $this->getSchemaFromFile('petstore.yaml');
    }

    /**
     * @param $name
     *
     * @return Schema
     */
    private function getSchemaFromFile($name)
    {
        $schemaFile = 'file://' . dirname(__DIR__) . '/fixtures/'.$name;
        $factory = new SwaggerSchemaFactory();

        return $factory->createSchema($schemaFile);
    }
}
