<?php

namespace Ronanchilvers\Db\Test\Model;

use Aura\SqlSchema\SchemaInterface;
use PDO;
use Ronanchilvers\Db\Model;
use Ronanchilvers\Db\Model\Metadata;
use Ronanchilvers\Db\Schema\SchemaFactory;
use Ronanchilvers\Db\Test\Fixture;
use Ronanchilvers\Db\Test\Model\MockModel;
use Ronanchilvers\Db\Test\Schema\MockSchema;
use Ronanchilvers\Db\Test\TestCase;

/**
 * Test suite for the model metadata class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class MetaDataTest extends TestCase
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockPdo()
    {
        return $this
            ->getMockBuilder('PDO')
            ->disableOriginalConstructor()
            ->getMock()
            ;
    }

    /**
     * Get a mock model instance
     *
     * @return Ronanchilvers\Db\Model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockModel()
    {
        return $this
            ->getMockBuilder(MockModel::class)
            ->getMock()
            ;
    }

    /**
     * Get a mock schema factory
     *
     * @return \Ronanchilvers\Db\Schema\SchemaFactory
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockSchemaFactory()
    {
        $schema = $this->createMock(SchemaInterface::class);
        $schema
            ->expects($this->once())
            ->method('fetchTableCols')
            ->willReturn(Fixture::load('schema/table'))
            ;
        $schemaFactory = $this->createMock(SchemaFactory::class);
        $schemaFactory
            ->expects($this->once())
            ->method('factory')
            ->willReturn($schema);

        return $schemaFactory;
    }

    /**
     * Get a new test instance
     *
     * @param \Ronanchilvers\Db\Schema\SchemaFactory $schemaFactory
     * @return \Ronanchilvers\Db\Model\Metadata
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newInstance($schemaFactory = null)
    {
        return new Metadata(
            $this->mockPdo(),
            new MockModel(),
            $schemaFactory
        );
    }

    /**
     * Test that metadata can return the mode class
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testMetadataCanGetModelClass()
    {
        $instance = $this->newInstance();
        $this->assertEquals(MockModel::class, $instance->class());
    }

    /**
     * Test that the metadata can return the model table
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testMetadataCanGetModelTable()
    {
        $instance = $this->newInstance();
        $this->assertEquals('mock_models', $instance->table());
    }

    /**
     * Test that the metadata can return the model table columns
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testMetadataCanGetModelTableColumns()
    {
        $schemaFactory = $this->mockSchemaFactory();
        $instance = $this->newInstance($schemaFactory);
        $result = $instance->columns();
        $this->assertEquals(2, count($result));
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('primary', $result['id']);
        $this->assertArrayHasKey('type', $result['id']);
        $this->assertArrayHasKey('length', $result['id']);
        $this->assertTrue($result['id']['primary']);
        $this->assertEquals('integer', $result['id']['type']);
        $this->assertEquals(11, $result['id']['length']);
        $this->assertArrayHasKey('field_1', $result);
        $this->assertArrayHasKey('primary', $result['field_1']);
        $this->assertArrayHasKey('type', $result['field_1']);
        $this->assertArrayHasKey('length', $result['field_1']);
        $this->assertFalse($result['field_1']['primary']);
        $this->assertEquals('varchar', $result['field_1']['type']);
        $this->assertEquals(256, $result['field_1']['length']);
    }

    /**
     * Test that the meta data can get the primary key
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testMetadataCanGetPrimaryKey()
    {
        $schemaFactory = $this->mockSchemaFactory();
        $instance = $this->newInstance($schemaFactory);

        $this->assertEquals('id', $instance->primaryKey());
    }

    /**
     * Test that meta data can identify a column is present
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testMetadataCanIdentifyColumnsPresent()
    {
        $schemaFactory = $this->mockSchemaFactory();
        $instance = $this->newInstance($schemaFactory);

        $this->assertTrue($instance->hasColumn('id'));
        $this->assertTrue($instance->hasColumn('field_1'));
        $this->assertFalse($instance->hasColumn('foobar'));
    }

    /**
     * Test that meta data can prefix a field correctly by default
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testMetadataCanPrefixFieldByDefault()
    {
        $instance = $this->newInstance();

        $this->assertEquals('id', $instance->prefix('id'));
        $this->assertEquals('field_1', $instance->prefix('field_1'));
    }

    /**
     * Test that meta data can prefix a field with a given prefix
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testMetadataCanPrefixFieldWithGivenPrefix()
    {
        $instance = new class(
            $this->mockPdo(),
            new MockModel()
        ) extends Metadata {
            protected $fieldPrefix = 'foobar';
        };
        $this->assertEquals('foobar_id', $instance->prefix('id'));
        $this->assertEquals('foobar_field_1', $instance->prefix('field_1'));
        $this->assertEquals('foobar_id', $instance->prefix('foobar_id'));
        $this->assertEquals('foobar_field_1', $instance->prefix('foobar_field_1'));
    }

    /**
     * Test that meta data can unprefix a field with a given prefix
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testMetadataCanUnprefixAFieldWithGivenPrefix()
    {
        $instance = new class(
            $this->mockPdo(),
            new MockModel()
        ) extends Metadata {
            protected $fieldPrefix = 'foobar';
        };
        $this->assertEquals('id', $instance->unprefix('foobar_id'));
        $this->assertEquals('field_1', $instance->unprefix('foobar_field_1'));
        $this->assertEquals('id', $instance->unprefix('id'));
        $this->assertEquals('field_1', $instance->unprefix('field_1'));
    }
}
