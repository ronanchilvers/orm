<?php

namespace Ronanchilvers\Orm\Test\Model;

use ClanCats\Hydrahon\Query\Sql\Update;
use PDO;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\QueryBuilder;
use Ronanchilvers\Orm\Test\Model\MockModel;
use Ronanchilvers\Orm\Test\TestCase;
use RuntimeException;

/**
 * Test suite for the model::save() method when updating
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class ModelUpdateTest extends TestCase
{
    /**
     * @var PDO
     */
    protected $mockPDO;

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setUp(): void
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
    protected function newInstance()
    {
        $builder = $this->getMockBuilder(UpdateModel::class);
        $builder->setMethods([
            'newQueryBuilder'
        ]);
        $instance = $builder->getMock();

        return $instance;
    }

    /**
     * Test updating an existing model
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testUpdatingExistingModel()
    {
        $data = ['id' => 1, 'field_1' => 'foobar'];
        $mockUpdate = $this->createMock(Update::class);
        $mockUpdate
            ->expects($this->once())
            ->method('set')
            ->with($data)
            ->willReturn($mockUpdate)
            ;
        $mockUpdate
            ->expects($this->once())
            ->method('where')
            ->with('id', '=', 1)
            ->willReturn($mockUpdate)
            ;
        $mockUpdate
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $mockQueryBuilder = $this->mockQueryBuilder();
        $mockQueryBuilder
            ->expects($this->once())
            ->method('update')
            ->willReturn($mockUpdate);
        $instance = $this->newInstance();
        $instance
            ->expects($this->once())
            ->method('newQueryBuilder')
            ->willReturn($mockQueryBuilder)
            ;
        $this->mockPDO
            ->expects($this->never())
            ->method('lastInsertId');

        $this->assertTrue($instance->save());
        $this->assertEquals(1, $instance->getId());
    }

    /**
     * Test that save returns false if insert fails
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testUpdatingReturnsFalseIfQueryFails()
    {
        $data = ['id' => 1, 'field_1' => 'foobar'];
        $mockUpdate = $this->createMock(Update::class);
        $mockUpdate
            ->expects($this->once())
            ->method('set')
            ->with($data)
            ->willReturn($mockUpdate)
            ;
        $mockUpdate
            ->expects($this->once())
            ->method('where')
            ->with('id', '=', 1)
            ->willReturn($mockUpdate)
            ;
        $mockUpdate
            ->expects($this->once())
            ->method('execute')
            ->willReturn(false);
        $mockQueryBuilder = $this->mockQueryBuilder();
        $mockQueryBuilder
            ->expects($this->once())
            ->method('update')
            ->willReturn($mockUpdate);
        $instance = $this->newInstance();
        $instance
            ->expects($this->once())
            ->method('newQueryBuilder')
            ->willReturn($mockQueryBuilder)
            ;
        $this->mockPDO
            ->expects($this->never())
            ->method('lastInsertId');

        $this->assertFalse($instance->save());
    }
}

/**
 * Mock model for testing with
 *
 * @author me
 */
class UpdateModel extends Model
{
    protected $data = [
        'id' => 1,
        'field_1' => 'foobar'
    ];
}
