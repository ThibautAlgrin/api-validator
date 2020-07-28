<?php declare(strict_types=1);

namespace ElevenLabs\Api;

use ElevenLabs\Api\Definition\RequestDefinition;
use ElevenLabs\Api\Definition\RequestDefinitions;
use Rize\UriTemplate;

/**
 * Class Schema.
 */
class Schema implements \Serializable
{
    /**
     * @var RequestDefinitions
     */
    private $requestDefinitions = [];

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var array
     */
    private $schemes;

    /**
     * Schema constructor.
     *
     * @param RequestDefinitions $requestDefinitions
     * @param string             $basePath
     * @param string             $host
     * @param array              $schemes
     */
    public function __construct(
        RequestDefinitions $requestDefinitions,
        string $basePath = '',
        string $host = '',
        array $schemes = ['http']
    ) {
        $this->requestDefinitions = $requestDefinitions;
        $this->host = $host;
        $this->basePath = $basePath;
        $this->schemes = $schemes;
    }

    /**
     * Find the operationId associated to a given path and method.
     *
     * @param string $method
     * @param string $path
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function findOperationId(string $method, string $path): string
    {
        foreach ($this->requestDefinitions as $requestDefinition) {
            if ($requestDefinition->getMethod() !== $method) {
                continue;
            }

            if ($this->isMatchingPath($requestDefinition->getPathTemplate(), $path)) {
                return $requestDefinition->getOperationId();
            }
        }

        throw new \InvalidArgumentException(sprintf('Unable to resolve the operationId for path %s', $path));
    }

    /**
     * @return RequestDefinitions
     */
    public function getRequestDefinitions(): RequestDefinitions
    {
        return $this->requestDefinitions;
    }

    /**
     * @param string $operationId
     *
     * @return RequestDefinition
     */
    public function getRequestDefinition(string $operationId): RequestDefinition
    {
        return $this->requestDefinitions->getRequestDefinition($operationId);
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return string[]
     */
    public function getSchemes(): array
    {
        return $this->schemes;
    }

    // Serializable
    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            'host' => $this->host,
            'basePath' => $this->basePath,
            'schemes' => $this->schemes,
            'requests' => $this->requestDefinitions,
        ]);
    }

    // Serializable
    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->host = $data['host'];
        $this->basePath = $data['basePath'];
        $this->schemes = $data['schemes'];
        $this->requestDefinitions = $data['requests'];
    }

    /**
     * @param string $pathTemplate
     * @param string $requestPath
     *
     * @return bool
     */
    private function isMatchingPath(string $pathTemplate, string $requestPath): bool
    {
        if ($pathTemplate === $requestPath) {
            return true;
        }

        return null !== (new UriTemplate())->extract($pathTemplate, $requestPath, true);
    }
}
