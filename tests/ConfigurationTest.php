<?php

namespace Ronanchilvers\Orm\Test;

use PHPUnit\Framework\TestCase;
use Ronanchilvers\Orm\Configuration;

/**
 * Test case for configuration objects
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ConfigurationTest extends TestCase
{
    /**
     * Get a new instance to test
     *
     * @return Ronanchilvers\Orm\Configuration
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newInstance($config = [])
    {
        return new Configuration($config);
    }

    /**
     * Test that by default null is returned for missing keys
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testByDefaultNullIsReturnedForMissingKeys()
    {
        $instance = $this->newInstance();

        $this->assertNull($instance->get('foobar'));
    }

    /**
     * Test that a default can be given for a missing key
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testADefaultCanBeGivenForAMissingKey()
    {
        $instance = $this->newInstance();

        $this->assertNull($instance->get('foobar'));
        $this->assertEquals('barbar', $instance->get('foobar', 'barbar'));
    }

    /**
     * Test that data can be supplied to the configuration and returned
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testDataCanBeAddedToTheConfiguration()
    {
        $instance = $this->newInstance(['foobar' => 'barbar']);

        $this->assertEquals('barbar', $instance->get('foobar'));
    }

    /**
     * Test that provided defaults are not returned when a key exists
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testDefaultsAreNotReturnedForExistingKey()
    {
        $instance = $this->newInstance(['foobar' => 'barbar']);

        $this->assertEquals('barbar', $instance->get('foobar', 'foofoo'));
    }
}
