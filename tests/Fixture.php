<?php

namespace Ronanchilvers\Orm\Test;

/**
 * Simple fixture loading class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Fixture
{
    /**
     * Load a fixture and return the data
     *
     * @param string $name
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function load($name)
    {
        $path = __DIR__ . '/../fixtures/' . $name . '.json';
        $contents = file_get_contents($path);

        return json_decode($contents);
    }
}
