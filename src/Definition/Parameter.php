<?php declare(strict_types=1);

namespace ElevenLabs\Api\Definition;

/**
 * Class Parameter.
 */
class Parameter implements \Serializable
{
    const LOCATIONS = ['path', 'header', 'query', 'body', 'formData'];
    const BODY_LOCATIONS = ['formData', 'body'];
    const BODY_LOCATIONS_TYPES = ['formData' => 'application/x-www-form-urlencoded', 'body'  => 'application/json'];

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $required;

    /**
     * @var ?\stdClass
     */
    private $schema;

    /**
     * Parameter constructor.
     *
     * @param string         $location
     * @param string         $name
     * @param bool           $required
     * @param \stdClass|null $schema
     */
    public function __construct(string $location, string $name, bool $required = false, ?\stdClass $schema = null)
    {
        if (!in_array($location, self::LOCATIONS, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s is not a supported parameter location, supported: %s',
                    $location,
                    implode(', ', self::LOCATIONS)
                )
            );
        }

        $this->location = $location;
        $this->name = $name;
        $this->required = $required;
        $this->schema = $schema;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return \stdClass|null
     */
    public function getSchema(): ?\stdClass
    {
        return $this->schema;
    }

    /**
     * @return bool
     */
    public function hasSchema(): bool
    {
        return null !== $this->schema;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'location' => $this->location,
            'name' => $this->name,
            'required' => $this->required,
            'schema' => $this->schema,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->location = $data['location'];
        $this->name = $data['name'];
        $this->required = $data['required'];
        $this->schema = $data['schema'];
    }
}
