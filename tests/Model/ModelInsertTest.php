<?php

namespace Ronanchilvers\Db\Test\Model;

use ClanCats\Hydrahon\Query\Sql\Insert;
use PDO;
use Ronanchilvers\Db\Model;
use Ronanchilvers\Db\Model\Metadata;
use Ronanchilvers\Db\QueryBuilder;
use Ronanchilvers\Db\Test\Model\MockModel;
use Ronanchilvers\Db\Test\TestCase;
use RuntimeException;

/**
 * Test suite for the model::save() method when inserting
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class ModelInsertTest extends TestCase
{
    /**
     * @var PDO
     */
    protected $mockPDO;

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setUp()
    {
        $this->mockPDO = $this->mockPDO();
        Model::setPdo($this->mockPDO);
    }

    /**
     * Get a mock query builder
     *
     * @return Ronanchilvers\Db\QueryBuilder
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockQueryBuilder()
    {
        return $this
            ->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
            ;
    }

    /**
     * Get a new test instance
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newInstance()
    {
        $mockMetadata = $this->createMock(Metadata::class);
        $mockMetadata
            ->expects($this->any())
            ->method('prefix')
            ->willReturnCallback(function ($value) {
                return $value;
            });
        $mockMetadata
            ->expects($this->once())
            ->method('hasColumn')
            ->with('field_1')
            ->willReturn(true);
        $mockMetadata
            ->expects($this->any())
            ->method('primaryKey')
            ->willReturn('id');
        $builder = $this->getMockBuilder(Model::class);
        $builder->setMethods([
            'metaData',
            'newQueryBuilder'
        ]);
        $instance = $builder->getMock();
        $instance
            ->expects($this->any())
            ->method('metaData')
            ->willReturn($mockMetadata)
            ;

        return $instance;
    }

    /**
     * Test inserting a new model
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testInsertingNewModel()
    {
        $data = ['field_1' => 'foobar'];
        $mockInsert = $this->createMock(Insert::class);
        $mockInsert
            ->expects($this->once())
            ->method('values')
            ->with($data)
            ->willReturn($mockInsert)
            ;
        $mockInsert
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $mockQueryBuilder = $this->mockQueryBuilder();
        $mockQueryBuilder
            ->expects($this->once())
            ->method('insert')
            ->willReturn($mockInsert);
        $instance = $this->newInstance();
        $instance
            ->expects($this->once())
            ->method('newQueryBuilder')
            ->willReturn($mockQueryBuilder)
            ;
        $this->mockPDO
            ->expects($this->once())
            ->method('lastInsertId')
            ->willReturn(1);

        $instance->setField_1('foobar');

        $this->assertTrue($instance->save());
        $this->assertEquals(1, $instance->getId());
    }

    /**
     * Test that save returns false if insert fails
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testInsertingReturnsFalseIfQueryFails()
    {
        $data = ['field_1' => 'foobar'];
        $mockInsert = $this->createMock(Insert::class);
        $mockInsert
            ->expects($this->once())
            ->method('values')
            ->with($data)
            ->willReturn($mockInsert)
            ;
        $mockInsert
            ->expects($this->once())
            ->method('execute')
            ->willReturn(false);
        $mockQueryBuilder = $this->mockQueryBuilder();
        $mockQueryBuilder
            ->expects($this->once())
            ->method('insert')
            ->willReturn($mockInsert);
        $instance = $this->newInstance();
        $instance
            ->expects($this->once())
            ->method('newQueryBuilder')
            ->willReturn($mockQueryBuilder)
            ;
        $this->mockPDO
            ->expects($this->never())
            ->method('lastInsertId');

        $instance->setField_1('foobar');
        $this->assertFalse($instance->save());
    }
}
