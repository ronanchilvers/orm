<?php

namespace Ronanchilvers\Orm\Test;

use ClanCats\Hydrahon\Query\Sql\Select;
use PDO;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\Model\AbstractObserver;
use Ronanchilvers\Orm\Model\Metadata;
use Ronanchilvers\Orm\Model\ObserverInterface;
use Ronanchilvers\Orm\QueryBuilder;
use Ronanchilvers\Orm\Test\Model\MockModel;
use Ronanchilvers\Orm\Test\TestCase;
use RuntimeException;

/**
 * Test suite for the model class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class ModelTest extends TestCase
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setUp(): void
    {
        Model::setPdo($this->mockPDO());
    }

    /**
     * Get a new test instance
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newInstance()
    {
        return new class () extends Model {};
    }

    /**
     * Test that model can return a new query builder
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testCanGetQueryBuilder()
    {
        $instance = $this->newInstance();
        $result = $instance->newQueryBuilder();

        $this->assertInstanceof(QueryBuilder::class, $result);
    }

    /**
     * Test that a column value can be set for a valid column
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testColumnCanBeSetForInvalidColumn()
    {
        $builder = $this->getMockBuilder(Model::class);
        $instance = $builder->getMock();

        $instance->setField_1('foobar');
        $this->assertEquals('foobar', $instance->getField_1());
    }

    /**
     * Test that setting an invalid column throws exception
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testSettingInvalidColumnThrowsException()
    {
        $this->expectException(RuntimeException::class);
        $mockMetadata = $this->createMock(Metadata::class);
        $mockMetadata
            ->expects($this->once())
            ->method('prefix')
            ->with('field_1')
            ->willReturn('field_1')
            ;
        $mockMetadata
            ->expects($this->once())
            ->method('hasColumn')
            ->with('field_1')
            ->willReturn(false);
        $mockMetadata
            ->expects($this->never())
            ->method('primaryKey');
        $builder = $this->getMockBuilder(Model::class);
        $builder->setMethods(['metaData']);
        $instance = $builder->getMock();
        $instance
            ->expects($this->exactly(2))
            ->method('metaData')
            ->willReturn($mockMetadata)
            ;

        $instance->setField_1('foobar');
    }

    /**
     * Test that setting the primary key throws an exception
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testSettingPrimaryKeyThrowsException()
    {
        $this->expectException(RuntimeException::class);
        $mockMetadata = $this->createMock(Metadata::class);
        $mockMetadata
            ->expects($this->once())
            ->method('prefix')
            ->with('id')
            ->willReturn('id')
            ;
        $mockMetadata
            ->expects($this->once())
            ->method('hasColumn')
            ->with('id')
            ->willReturn(true);
        $mockMetadata
            ->expects($this->once())
            ->method('primaryKey')
            ->willReturn('id')
            ;
        $builder = $this->getMockBuilder(Model::class);
        $builder->setMethods(['metaData']);
        $instance = $builder->getMock();
        $instance
            ->expects($this->exactly(3))
            ->method('metaData')
            ->willReturn($mockMetadata)
            ;

        $instance->setId(1);
    }

    /**
     * Test that getting an invalid column returns null
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testSettingInvalidColumnReturnsNull()
    {
        $instance = $this->newInstance();

        $this->assertNull($instance->getFoobar());
    }

    /**
     * Test that magic call triggers an error for non getter / setter methods
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testMagicCallTriggersErrorForUnknownMethods()
    {
        $this->expectException(RuntimeException::class);
        $instance = $this->newInstance();

        $instance->foobar();
    }

    /**
     * Test that magic static call passes methods to the query builder
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testMagicStaticCallPassesToQueryBuilder()
    {
        $this->assertInstanceof(Select::class, MockModel::select());
    }

    /**
     * Test that magic static call throws an exception for invalid methods
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testMagicStaticCallThrowsExceptionForInvalidMethods()
    {
        $this->expectException(RuntimeException::class);
        MockModel::foobar();
    }

    /**
     * Provider
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function observerEventProvider()
    {
        return [
            ['loaded'],
            ['creating'],
            ['created'],
            ['updating'],
            ['updated'],
            ['saving'],
            ['saved'],
            ['deleting'],
            ['deleted'],
        ];
    }

    /**
     * Test that an observer set on a model fires correctly
     *
     * @dataProvider observerEventProvider
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testObserversCanBeSetAndFired($event)
    {
        $mockModel = $this->createMock(MockModel::class);
        $mockObserver = $this->createMock(ObserverInterface::class);
        $mockObserver->expects($this->once())
            ->method($event)
            ->with($mockModel);
        MockModel::observe($mockObserver);
        MockModel::notifyObserversProxy(
            $mockModel,
            $event
        );
    }

    /**
     * Test that observer notification stops when an observer returns false
     *
     * @dataProvider observerEventProvider
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testReturningFalseStopsEventObservation($event)
    {
        $mockModel = $this->createMock(MockModel::class);
        $mockObserver1 = $this->createMock(ObserverInterface::class);
        $mockObserver2 = $this->createMock(ObserverInterface::class);
        $mockObserver1->expects($this->once())
            ->method($event)
            ->with($mockModel)
            ->willReturn(false);
        $mockObserver2->expects($this->never())
            ->method($event);
        MockModel::observe($mockObserver1);
        MockModel::observe($mockObserver2);
        MockModel::notifyObserversProxy(
            $mockModel,
            $event
        );
    }
}
