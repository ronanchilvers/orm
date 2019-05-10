<?php

namespace Ronanchilvers\Db\Test\Schema;

use Aura\SqlSchema\ColumnFactory;
use Aura\SqlSchema\MysqlSchema;
use InvalidArgumentException;
use PDO;
use Ronanchilvers\Db\Schema\SchemaFactory;
use Ronanchilvers\Db\Test\TestCase;

/**
 * Test cases for the aura.sqlschema factory
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class SchemaFactoryTest extends TestCase
{
    /**
     * Get a new instance to test
     *
     * @return \Ronanchilvers\Db\Schema\SchemaFactory
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newInstance()
    {
        return new SchemaFactory();
    }

    /**
     * Test that the schema factory returns the correct schema type
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testFactoryReturnsCorrectSchemaType()
    {
        $mockPDO = $this->mockPDO();
        $mockPDO->expects($this->once())
                ->method('getAttribute')
                ->with(PDO::ATTR_DRIVER_NAME)
                ->willReturn('MySQL');
        $instance = $this->newInstance();

        $this->assertInstanceof(MysqlSchema::class, $instance->factory($mockPDO));
    }

    /**
     * Test that requesting an unknown schema throws exception
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testFactoryThrowsExceptionForUnknownSchemaType()
    {
        $this->expectException(InvalidArgumentException::class);
        $mockPDO = $this->mockPDO();
        $mockPDO->expects($this->never())
                ->method('getAttribute')
                ;
        $instance = $this->newInstance();
        $instance->factory(
            $mockPDO,
            'foobar'
        );
    }

    /**
     * Test that schemas can be configured
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testSchemasCanBeConfigured()
    {
        $schemas = ['foo' => 'bar'];
        $instance = new SchemaFactory(null, $schemas);

        $this->assertEquals($schemas, $instance->schemas());
    }

    /**
     * Test that a column factory can be configured
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testColumnFactoryCanBeConfigured()
    {
        $columnFactory = new ColumnFactory();
        $instance = new SchemaFactory($columnFactory);

        $this->assertEquals($columnFactory, $instance->columnFactory());
    }
}
