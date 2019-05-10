<?php

namespace Ronanchilvers\Db\Test\Model;

use Ronanchilvers\Db\Model;

/**
 * Mock model subclass for use in testing
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class MockModel extends Model
{
    /**
     * Static proxy for notifyObservers to allow testing
     *
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function notifyObserversProxy(
        Model $model,
        string $event
    )
    {
        return static::notifyObservers($model, $event);
    }
}
