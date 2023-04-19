<?php

namespace Ronanchilvers\Orm\Features\Type;

/**
 * Type handler for array data
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class IntHandler implements HandlerInterface
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toType($raw, array $options = [])
    {
        return (int) $raw;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toRaw($typeData, array $options = [])
    {
        return (int) $typeData;
    }
}
