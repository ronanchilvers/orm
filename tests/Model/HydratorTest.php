<?php

namespace Ronanchilvers\Db\Test\Model;

use Ronanchilvers\Db\Model;
use Ronanchilvers\Db\Model\Hydrator;
use Ronanchilvers\Db\Test\TestCase;

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
        Model::setPdo($this->mockPDO());
    }

    /**
     * Get a new instance to test
     *
     * @return \Ronanchilvers\Db\Model\Hydrator
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

        $this->assertEquals(1, $model->getId());
        $this->assertEquals('foobar', $model->getField_1());
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
