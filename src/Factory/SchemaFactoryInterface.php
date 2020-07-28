<?php declare(strict_types=1);

namespace ElevenLabs\Api\Factory;

use ElevenLabs\Api\Schema;

/**
 * Interface SchemaFactoryInterface.
 */
interface SchemaFactoryInterface
{
    /**
     * @param string $schemaFile
     *
     * @return Schema
     */
    public function createSchema(string $schemaFile): Schema;
}
