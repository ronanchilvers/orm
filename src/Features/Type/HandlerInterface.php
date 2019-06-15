<?php

namespace Ronanchilvers\Orm\Features\Type;

/**
 * Interface for data type handlers
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface HandlerInterface
{
    /**
     * Transform a raw value to a type
     *
     * @param mixed $raw
     * @param array $options
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toType($raw, array $options = []);

    /**
     * Transform a data type to a raw value
     *
     * @param mixed $typeData
     * @param array $options
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toRaw($typeData, array $options = []);
}
