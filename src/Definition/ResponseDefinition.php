<?php declare(strict_types=1);

namespace ElevenLabs\Api\Definition;

/**
 * Class ResponseDefinition.
 */
class ResponseDefinition implements \Serializable, MessageDefinition
{
    /**
     * @var int|string
     */
    private $statusCode;

    /**
     * @var string[]
     */
    private $contentTypes;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * ResponseDefinition constructor.
     *
     * @param int|string $statusCode
     * @param string[]   $allowedContentTypes
     * @param Parameters $parameters
     */
    public function __construct($statusCode, array $allowedContentTypes, Parameters $parameters)
    {
        $this->statusCode = $statusCode;
        $this->contentTypes = $allowedContentTypes;
        $this->parameters = $parameters;
    }

    /**
     * @return int|string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return Parameters
     */
    public function getParameters(): Parameters
    {
        return $this->parameters;
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
     * Supported response types.
     *
     * @return string[]
     */
    public function getContentTypes(): array
    {
        return $this->contentTypes;
    }

    // Serializable
    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            'statusCode' => $this->statusCode,
            'contentTypes' => $this->contentTypes,
            'parameters' => $this->parameters,
        ]);
    }

    // Serializable
    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->statusCode = $data['statusCode'];
        $this->contentTypes = $data['contentTypes'];
        $this->parameters = $data['parameters'];
    }
}
