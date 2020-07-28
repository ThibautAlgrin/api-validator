<?php declare(strict_types=1);

namespace ElevenLabs\Api\Definition;

/**
 * Class Parameters.
 */
class Parameters implements \Serializable, \IteratorAggregate
{
    /**
     * @var Parameter[]
     */
    private $parameters = [];

    /**
     * Parameters constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->addParameter($parameter);
        }
    }

    /**
     * @return iterable
     */
    public function getIterator(): iterable
    {
        foreach ($this->parameters as $name => $parameter) {
            yield $name => $parameter;
        }
    }

    /**
     * @return bool
     */
    public function hasBodySchema(): bool
    {
        $body = $this->getBody();

        return null !== $body && $body->hasSchema();
    }

    /**
     * @return \stdClass|null
     */
    public function getBodySchema(): ?\stdClass
    {
        return $this->getBody()->getSchema();
    }

    /**
     * @return bool
     */
    public function hasPathSchema(): bool
    {
        return null !== $this->getPathSchema();
    }

    /**
     * @return \stdClass|null
     */
    public function getPathSchema(): ?\stdClass
    {
        return $this->getSchema($this->getPath());
    }

    /**
     * @return bool
     */
    public function hasQueryParametersSchema(): bool
    {
        return null !== $this->getQueryParametersSchema();
    }

    /**
     * JSON Schema for the query parameters.
     *
     * @return \stdClass|null
     */
    public function getQueryParametersSchema(): ?\stdClass
    {
        return $this->getSchema($this->getQuery());
    }

    /**
     * @return bool
     */
    public function hasHeadersSchema(): bool
    {
        return null !== $this->getHeadersSchema();
    }

    /**
     * JSON Schema for the headers.
     *
     * @return \stdClass|null
     */
    public function getHeadersSchema(): ?\stdClass
    {
        return $this->getSchema($this->getHeaders());
    }

    /**
     * @return Parameter[]
     */
    public function getPath(): array
    {
        return $this->findByLocation('path');
    }

    /**
     * @return Parameter[]
     */
    public function getQuery(): array
    {
        return $this->findByLocation('query');
    }

    /**
     * @return Parameter[]
     */
    public function getHeaders(): array
    {
        return $this->findByLocation('header');
    }

    /**
     * @return Parameter|null
     */
    public function getBody(): ?Parameter
    {
        $match = $this->findByLocation('body');
        if (empty($match)) {
            return null;
        }

        return current($match);
    }

    /**
     * @param string $name
     *
     * @return Parameter|null
     */
    public function getByName(string $name): ?Parameter
    {
        if (!isset($this->parameters[$name])) {
            return null;
        }

        return $this->parameters[$name];
    }

    // Serializable
    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize(['parameters' => $this->parameters]);
    }

    // Serializable
    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->parameters = $data['parameters'];
    }

    /**
     * @param Parameter[] $parameters
     *
     * @return \stdClass|null
     */
    private function getSchema(array $parameters): ?\stdClass
    {
        if (empty($parameters)) {
            return null;
        }

        $schema = new \stdClass();
        $schema->type = 'object';
        $schema->required = [];
        $schema->properties = new \stdClass();
        foreach ($parameters as $name => $parameter) {
            if ($parameter->isRequired()) {
                $schema->required[] = $parameter->getName();
            }
            $schema->properties->{$name} = $parameter->getSchema();
        }

        return $schema;
    }

    /**
     * @param string $location
     *
     * @return Parameter[]
     */
    private function findByLocation(string $location): array
    {
        return array_filter(
            $this->parameters,
            function (Parameter $parameter) use ($location) {
                return $parameter->getLocation() === $location;
            }
        );
    }

    /**
     * @param Parameter $parameter
     */
    private function addParameter(Parameter $parameter)
    {
        $this->parameters[$parameter->getName()] = $parameter;
    }
}
