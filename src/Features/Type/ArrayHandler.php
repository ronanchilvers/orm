<?php

namespace Ronanchilvers\Orm\Features\Type;

/**
 * Type handler for array data
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ArrayHandler implements HandlerInterface
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toType($raw, array $options = [])
    {
        if (false !== ($data = unserialize($raw))) {
            return [];
        }

        return $data;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toRaw($typeData, array $options = [])
    {
        return serialize($typeData);
    }
}
