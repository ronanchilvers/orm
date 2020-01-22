<?php

namespace Ronanchilvers\Orm\Traits;

use Ronanchilvers\Orm\Model;

/**
 * Base trait for model decorator objects
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait DecoratorTrait
{
    /**
     * @var \Ronanchilvers\Orm\Model
     */
    protected $model = null;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Magic get to proxy all accessors
     *
     * @param string $attribute
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __get($attribute)
    {
        return $this->model->__get($attribute);
    }

    /**
     * Magic set to proxy all setters
     *
     * @param string $key
     * @param mixed $value
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __set($attribute, $value)
    {
        return $this->model->__set($attribute, $value);
    }

    /**
     * Magic property isset
     *
     * @param string $attribute
     * @return bool
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __isset($attribute)
    {
        return $this->model->__isset($attribute);
    }

    /**
     * Proxy all method calls to the underlying model
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->model, $method], $args);
    }

    /**
     * Get the decorated model instance
     *
     * @return Model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function model()
    {
        return $this->model;
    }
}
