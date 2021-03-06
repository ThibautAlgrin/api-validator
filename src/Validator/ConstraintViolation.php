<?php declare(strict_types=1);

namespace ElevenLabs\Api\Validator;

/**
 * ValueObject that contains constraint violation properties
 *
 * Class ConstraintViolation.
 */
class ConstraintViolation
{
    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $constraint;

    /**
     * @var string
     */
    private $location;

    /**
     * ConstraintViolation constructor.
     *
     * @param string $property
     * @param string $message
     * @param string $constraint
     * @param string $location
     */
    public function __construct(
        string $property,
        string $message,
        string $constraint,
        string $location
    ) {
        $this->property = $property;
        $this->message = $message;
        $this->constraint = $constraint;
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getConstraint(): string
    {
        return $this->constraint;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'property' => $this->getProperty(),
            'message' => $this->getMessage(),
            'constraint' => $this->getConstraint(),
            'location' => $this->getLocation(),
        ];
    }
}
