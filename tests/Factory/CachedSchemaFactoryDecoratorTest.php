<?php declare(strict_types=1);

namespace ElevenLabs\Api\Tests\Factory;

use ElevenLabs\Api\Factory\CachedSchemaFactoryDecorator;
use ElevenLabs\Api\Factory\SchemaFactoryInterface;
use ElevenLabs\Api\Schema;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class CachedSchemaFactoryDecoratorTest.
 */
class CachedSchemaFactoryDecoratorTest extends TestCase
{
    /** @test */
    public function itShouldSaveASchemaInACacheStore()
    {
        $schemaFile = 'file://fake-schema.yml';
        $schema = $this->prophesize(Schema::class);

        $item = $this->prophesize(CacheItemInterface::class);
        $item->isHit()->shouldBeCalled()->willReturn(false);
        $item->set($schema)->shouldBeCalled()->willReturn($item);

        $cache = $this->prophesize(CacheItemPoolInterface::class);
        $cache->getItem('3f470a326a5926a2e323aaadd767c0e64302a080')->willReturn($item);
        $cache->save($item)->willReturn(true);

        $schemaFactory = $this->prophesize(SchemaFactoryInterface::class);
        $schemaFactory->createSchema($schemaFile)->willReturn($schema);

        $cachedSchema = new CachedSchemaFactoryDecorator(
            $cache->reveal(),
            $schemaFactory->reveal()
        );

        $expectedSchema = $schema->reveal();
        $actualSchema = $cachedSchema->createSchema($schemaFile);

        $this->assertInstanceOf(Schema::class, $actualSchema);
        $this->assertSame($expectedSchema, $actualSchema);
    }

    /** @test */
    public function itShouldLoadASchemaFromACacheStore()
    {
        $schemaFile = 'file://fake-schema.yml';
        $schema = $this->prophesize(Schema::class);

        $item = $this->prophesize(CacheItemInterface::class);
        $item->isHit()->shouldBeCalled()->willReturn(true);
        $item->get()->shouldBeCalled()->willReturn($schema);

        $cache = $this->prophesize(CacheItemPoolInterface::class);
        $cache->getItem('3f470a326a5926a2e323aaadd767c0e64302a080')->willReturn($item);

        $schemaFactory = $this->prophesize(SchemaFactoryInterface::class);
        $schemaFactory->createSchema(Argument::any())->shouldNotBeCalled();

        $cachedSchema = new CachedSchemaFactoryDecorator(
            $cache->reveal(),
            $schemaFactory->reveal()
        );

        $expectedSchema = $schema->reveal();
        $actualSchema = $cachedSchema->createSchema($schemaFile);

        $this->assertInstanceOf(Schema::class, $actualSchema);
        $this->assertSame($expectedSchema, $actualSchema);
    }
}
