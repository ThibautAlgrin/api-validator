<?php declare(strict_types=1);

namespace ElevenLabs\Api\Definition;

/**
 * Interface MessageDefinition.
 */
interface MessageDefinition
{
    /**
     * Get a list of supported content types.
     *
     * @return string[]
     */
    public function getContentTypes(): array;

    /**
     * Check if a schema for body is available.
     *
     * @return bool
     */
    public function hasBodySchema(): bool;

    /**
     * Get the schema for the body.
     *
     * @return \stdClass|null
     */
    public function getBodySchema(): ?\stdClass;

    /**
     * Check if a schema for headers is available.
     *
     * @return bool
     */
    public function hasHeadersSchema(): bool;

    /**
     * Get the schema for the headers.
     *
     * @return \stdClass|null
     */
    public function getHeadersSchema(): ?\stdClass;
}
