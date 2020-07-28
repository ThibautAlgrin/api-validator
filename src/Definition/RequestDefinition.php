<?php declare(strict_types=1);

namespace ElevenLabs\Api\Definition;

/**
 * Class RequestDefinition.
 */
class RequestDefinition implements \Serializable, MessageDefinition
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $operationId;

    /**
     * @var string
     */
    private $pathTemplate;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var string[]
     */
    private $contentTypes;

    /**
     * @var ResponseDefinition[]
     */
    private $responses;

    /**
     * @param string               $method
     * @param string               $operationId
     * @param string               $pathTemplate
     * @param Parameters           $parameters
     * @param string[]             $contentTypes
     * @param ResponseDefinition[] $responses
     */
    public function __construct(string $method, string $operationId, string $pathTemplate, Parameters $parameters, array $contentTypes, array $responses)
    {
        $this->method = $method;
        $this->operationId = $operationId;
        $this->pathTemplate = $pathTemplate;
        $this->parameters = $parameters;
        $this->contentTypes = $contentTypes;
        foreach ($responses as $response) {
            $this->addResponseDefinition($response);
        }
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->operationId;
    }

    /**
     * @return string
     */
    public function getPathTemplate(): string
    {
        return $this->pathTemplate;
    }

    /**
     * @return Parameters
     */
    public function getRequestParameters(): Parameters
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getContentTypes(): array
    {
        return $this->contentTypes;
    }

    /**
     * @param int|string $statusCode
     *
     * @return ResponseDefinition
     */
    public function getResponseDefinition($statusCode): ResponseDefinition
    {
        if (isset($this->responses[$statusCode])) {
            return $this->responses[$statusCode];
        }

        if (isset($this->responses['default'])) {
            return $this->responses['default'];
        }

        throw new \InvalidArgumentException(
            sprintf(
                'No response definition for %s %s is available for status code %s',
                $this->method,
                $this->pathTemplate,
                $statusCode
            )
        );
    }

    /**
     * @return bool
     */
    public function hasBodySchema(): bool
    {
        return $this->parameters->hasBodySchema();
    }

    /**
     * @return \stdClass|null
     */
    public function getBodySchema(): ?\stdClass
    {
        return $this->parameters->getBodySchema();
    }

    /**
     * @return bool
     */
    public function hasHeadersSchema(): bool
    {
        return $this->parameters->hasHeadersSchema();
    }

    /**
     * @return \stdClass|null
     */
    public function getHeadersSchema(): ?\stdClass
    {
        return $this->parameters->getHeadersSchema();
    }

    /**
     * @return bool
     */
    public function hasPathSchema(): bool
    {
        return $this->parameters->hasPathSchema();
    }

    /**
     * @return \stdClass|null
     */
    public function getPathSchema(): ?\stdClass
    {
        return $this->parameters->getPathSchema();
    }

    /**
     * @return bool
     */
    public function hasQueryParametersSchema(): bool
    {
        return $this->parameters->hasQueryParametersSchema();
    }

    /**
     * @return \stdClass|null
     */
    public function getQueryParametersSchema(): ?\stdClass
    {
        return $this->parameters->getQueryParametersSchema();
    }

    // Serializable
    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            'method' => $this->method,
            'operationId' => $this->operationId,
            'pathTemplate' => $this->pathTemplate,
            'parameters' => $this->parameters,
            'contentTypes' => $this->contentTypes,
            'responses' => $this->responses,
        ]);
    }

    // Serializable
    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->method = $data['method'];
        $this->operationId = $data['operationId'];
        $this->pathTemplate = $data['pathTemplate'];
        $this->parameters = $data['parameters'];
        $this->contentTypes = $data['contentTypes'];
        $this->responses = $data['responses'];
    }

    /**
     * @param ResponseDefinition $response
     */
    private function addResponseDefinition(ResponseDefinition $response)
    {
        $this->responses[$response->getStatusCode()] = $response;
    }
}
