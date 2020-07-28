<?php declare(strict_types=1);

namespace ElevenLabs\Api\Definition;

/**
 * Class RequestDefinitions.
 */
class RequestDefinitions implements \Serializable, \IteratorAggregate
{
    /**
     * @var RequestDefinition[]
     */
    private $definitions = [];

    /**
     * RequestDefinitions constructor.
     *
     * @param array $requestDefinitions
     */
    public function __construct(array $requestDefinitions = [])
    {
        foreach ($requestDefinitions as $requestDefinition) {
            $this->addRequestDefinition($requestDefinition);
        }
    }

    /**
     * @param string $operationId
     *
     * @throws \InvalidArgumentException
     *
     * @return RequestDefinition
     */
    public function getRequestDefinition(string $operationId): RequestDefinition
    {
        if (isset($this->definitions[$operationId])) {
            return $this->definitions[$operationId];
        }

        throw new \InvalidArgumentException(sprintf('Unable to find request definition for operationId %s', $operationId));
    }

    // IteratorAggregate

    /**
     * @return iterable
     */
    public function getIterator(): iterable
    {
        foreach ($this->definitions as $operationId => $requestDefinition) {
            yield $operationId => $requestDefinition;
        }
    }

    // Serializable
    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            'definitions' => $this->definitions,
        ]);
    }

    // Serializable
    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->definitions = $data['definitions'];
    }

    /**
     * @param RequestDefinition $requestDefinition
     */
    private function addRequestDefinition(RequestDefinition $requestDefinition): void
    {
        $this->definitions[$requestDefinition->getOperationId()] = $requestDefinition;
    }
}
