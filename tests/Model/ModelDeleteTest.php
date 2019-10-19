<?php

namespace Ronanchilvers\Orm\Test\Model;

use ClanCats\Hydrahon\Query\Sql\Delete;
use PDO;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\QueryBuilder;
use Ronanchilvers\Orm\Test\Model\MockModel;
use Ronanchilvers\Orm\Test\TestCase;
use RuntimeException;

/**
 * Test suite for the model::delete() method
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class ModelDeleteTest extends TestCase
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
     * @return \Ronanchilvers\Orm\QueryBuilder
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
    protected function newInstance($class = DeleteModel::class)
    {
        $builder = $this->getMockBuilder($class);
        $builder->setMethods([
            'newQueryBuilder'
        ]);
        $instance = $builder->getMock();

        return $instance;
    }

    /**
     * Test inserting a new model
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testDeletingExistingModel()
    {
        $mockDelete = $this->createMock(Delete::class);
        $mockDelete
            ->expects($this->once())
            ->method('where')
            ->with('id', '=', 1)
            ->willReturn($mockDelete)
            ;
        $mockDelete
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $mockQueryBuilder = $this->mockQueryBuilder();
        $mockQueryBuilder
            ->expects($this->once())
            ->method('delete')
            ->willReturn($mockDelete);
        $instance = $this->newInstance();
        $instance
            ->expects($this->once())
            ->method('newQueryBuilder')
            ->willReturn($mockQueryBuilder)
            ;

        $this->assertTrue($instance->delete());
        $this->assertEmpty($instance->getId());
    }

    /**
     * Test that save returns false if insert fails
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testDeletingReturnsFalseIfQueryFails()
    {
        $mockDelete = $this->createMock(Delete::class);
        $mockDelete
            ->expects($this->once())
            ->method('where')
            ->with('id', '=', 1)
            ->willReturn($mockDelete)
            ;
        $mockDelete
            ->expects($this->once())
            ->method('execute')
            ->willReturn(false);
        $mockQueryBuilder = $this->mockQueryBuilder();
        $mockQueryBuilder
            ->expects($this->once())
            ->method('delete')
            ->willReturn($mockDelete);
        $instance = $this->newInstance();
        $instance
            ->expects($this->once())
            ->method('newQueryBuilder')
            ->willReturn($mockQueryBuilder)
            ;

        $this->assertFalse($instance->delete());
    }

    /**
     * Test that a non loaded model cannot be deleted
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testNonLoadedModelCannotBeDelete()
    {
        $this->expectException(RuntimeException::class);
        $instance = $this->newInstance(Model::class);
        $instance->delete();
    }
}

/**
 * Mock model for testing with
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class DeleteModel extends Model
{
    protected $data = [
        'id' => 1
    ];
}

