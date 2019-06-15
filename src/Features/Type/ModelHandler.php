<?php

namespace Ronanchilvers\Orm\Features\Type;

use Carbon\Carbon;
use Ronanchilvers\Orm\Model;

/**
 * Type handler for model data
 *
 * This handler isn't intended to provide relation handling. It simply is able to
 * translate a model input into an integer for storage in model data but it can't
 * transform an id into a model.
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ModelHandler implements HandlerInterface
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toType($raw, array $options = [])
    {
        return $raw;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toRaw($typeData, array $options = [])
    {
        if (!$typeData instanceof Model) {
            return $typeData;
        }

        return $typeData->getAttribute(
            $typeData->primaryKey()
        );
    }
}
