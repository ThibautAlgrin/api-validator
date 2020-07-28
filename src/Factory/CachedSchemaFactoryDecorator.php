<?php declare(strict_types=1);

namespace ElevenLabs\Api\Factory;

use ElevenLabs\Api\Schema;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class CachedSchemaFactoryDecorator.
 */
class CachedSchemaFactoryDecorator implements SchemaFactoryInterface
{
    /**
     * @var SchemaFactoryInterface
     */
    private $schemaFactory;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * CachedSchemaFactoryDecorator constructor.
     *
     * @param CacheItemPoolInterface $cache
     * @param SchemaFactoryInterface $schemaFactory
     */
    public function __construct(CacheItemPoolInterface $cache, SchemaFactoryInterface $schemaFactory)
    {
        $this->cache = $cache;
        $this->schemaFactory = $schemaFactory;
    }

    /**
     * @param string $schemaFile
     *
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return Schema
     */
    public function createSchema(string $schemaFile): Schema
    {
        $cacheKey = hash('sha1', $schemaFile);
        $item = $this->cache->getItem($cacheKey);
        if ($item->isHit()) {
            $schema = $item->get();
        } else {
            $schema = $this->schemaFactory->createSchema($schemaFile);
            $this->cache->save($item->set($schema));
        }

        return $schema;
    }
}
