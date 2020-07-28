<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests\Normalizer;

use ElevenLabs\Api\Normalizer\QueryParamsNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * Class QueryParamsNormalizerTest.
 */
class QueryParamsNormalizerTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider getValidQueryParameters
     */
    public function itNormalizeQueryParameters($schemaType, $actualValue, $expectedValue)
    {
        $jsonSchema = $this->toObject([
            'type' => 'object',
            'properties' => [
                'param' => [
                    'type' => $schemaType,
                ],
            ],
        ]);

        $normalizedValue = QueryParamsNormalizer::normalize(['param' => $actualValue], $jsonSchema);

        $this->assertSame($expectedValue, $normalizedValue['param']);
    }

    /**
     * @return array
     */
    public function getValidQueryParameters()
    {
        return [
            // description => [schemaType, actual, expected]
            'with an integer' => ['integer', '123', 123],
            'with a number' => ['number', '12.15', 12.15],
            'with true given as a string' => ['boolean', 'true', true],
            'with true given as a numeric' => ['boolean', '1', true],
            'with false given as a string' => ['boolean', 'false', false],
            'with false given as a numeric string' => ['boolean', '0', false],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getValidCollectionFormat
     */
    public function itTransformCollectionFormatIntoArray($collectionFormat, $rawValue, array $expectedValue)
    {
        $jsonSchema = $this->toObject([
            'type' => 'object',
            'properties' => [
                'param' => [
                    'type' => 'array',
                    'items' => ['string'],
                    'collectionFormat' => $collectionFormat,
                ],
            ],
        ]);

        $normalizedValue = QueryParamsNormalizer::normalize(['param' => $rawValue], $jsonSchema);

        $this->assertSame($expectedValue, $normalizedValue['param']);
    }

    /**
     * @return array
     */
    public function getValidCollectionFormat()
    {
        return [
            'with csv' => ['csv', 'foo,bar,baz', ['foo', 'bar', 'baz']],
            'with ssv' => ['ssv', 'foo bar baz', ['foo', 'bar', 'baz']],
            'with pipes' => ['pipes', 'foo|bar|baz', ['foo', 'bar', 'baz']],
            'with tabs' => ['tsv', "foo\tbar\tbaz", ['foo', 'bar', 'baz']],
        ];
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     *
     * @expectedExceptionMessage unknown is not a supported query collection format
     */
    public function itThrowAnExceptionOnUnsupportedCollectionFormat()
    {
        $jsonSchema = $this->toObject([
            'type' => 'object',
            'properties' => [
                'param' => [
                    'type' => 'array',
                    'items' => ['string'],
                    'collectionFormat' => 'unknown',
                ],
            ],
        ]);

        QueryParamsNormalizer::normalize(['param' => 'foo%bar'], $jsonSchema);
    }

    /**
     * @param array $array
     *
     * @return mixed
     */
    private function toObject(array $array)
    {
        return json_decode(json_encode($array));
    }
}
