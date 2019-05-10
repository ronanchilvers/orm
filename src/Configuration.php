<?php

namespace Ronanchilvers\Db;

use Ronanchilvers\Db\ConfigurationInterface;

/**
 * Main configuration object
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Class constructor
     *
     * @param array $config
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(array $config = null)
    {
        if (is_array($config)) {
            $this->data = array_merge(
                $this->defaults,
                $config
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function get($key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }
}
