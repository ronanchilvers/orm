<?php

namespace Ronanchilvers\Orm\Test\Model;

use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\Model\Hydrator;
use Ronanchilvers\Orm\Orm;
use Ronanchilvers\Orm\Test\TestCase;

/**
 * Test suite for the standard model hydrator
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class HydratorTest extends TestCase
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setUp()
    {
        Orm::setConnection($this->mockPDO());
    }

    /**
     * Get a new instance to test
     *
     * @return \Ronanchilvers\Orm\Model\Hydrator
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newInstance()
    {
        return new Hydrator();
    }

    /**
     * Test that a model can be hydrated correctly
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testModelCanBeHydrated()
    {
        $data  = [
            'id'      => 1,
            'field_1' => 'foobar',
        ];
        $model = new HydrateModel();
        $instance = $this->newInstance();
        $instance->hydrate(
            $data,
            $model
        );

        $this->assertEquals(1, $model->id);
        $this->assertEquals('foobar', $model->field_1);
    }

    /**
     * Test that a model can be dehydrated
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testModelCanBeDehydrated()
    {
        $model = new DehydrateModel();
        $instance = $this->newInstance();
        $data = [
            'id'      => 1,
            'field_1' => 'foobar',
        ];
        $this->assertEquals($data, $instance->dehydrate($model));
    }
}

/**
 * Test model for dehydrating
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class HydrateModel extends Model
{
}

/**
 * Test model for dehydrating
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class DehydrateModel extends Model
{
    /**
     * @var array
     */
    protected $data = [
        'id'        => 1,
        'field_1'   => 'foobar'
    ];
}
