<?php

namespace Ronanchilvers\Db;

/**
 * Interface for configuration object
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface ConfigurationInterface
{
    /**
     * Get a configuration option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function get($key, $default = null);
}
