<?php

namespace Ronanchilvers\Db\Model;

use Ronanchilvers\Db\Model;

/**
 * Hydrator for models
 *
 * @todo Fix this so that it can pass unit tests
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Hydrator
{
    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct()
    {}

    /**
     * Hydrate a model from an array
     *
     * @param array $data
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function hydrate(array $array, Model $model)
    {
        $closure = function ($data) {
            $this->data = $data;
            static::notifyObservers($this, 'loaded');
        };
        $hydrator = $closure->bindTo($model, $model);
        $hydrator($array);
    }

    /**
     * Dehydrate a model to an array
     *
     * @param \Ronanchilvers\Db\Model $model
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function dehydrate(Model $model)
    {
        $closure = function () {
            return $this->data;
        };
        $dehydrator = $closure->bindTo($model, $model);

        return $dehydrator();
    }
}
