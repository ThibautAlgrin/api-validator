<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests\Validator;

use ElevenLabs\Api\Validator\ConstraintViolation;
use PHPUnit\Framework\TestCase;

/**
 * Class ConstraintViolationTest.
 */
class ConstraintViolationTest extends TestCase
{
    public function testConstraintViolationToArray()
    {
        $expectedArray = [
            'property' => 'property_one',
            'message' => 'a violation message',
            'constraint' => 'required',
            'location' => 'query',
        ];

        $violation = new ConstraintViolation('property_one', 'a violation message', 'required', 'query');

        $this->assertSame($expectedArray, $violation->toArray());
    }
}
