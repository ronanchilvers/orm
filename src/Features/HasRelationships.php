<?php

namespace Ronanchilvers\Orm\Features;

use Ronanchilvers\Orm\Features\Relationship\BelongsTo;
use Ronanchilvers\Orm\Features\Relationship\HasMany;
use Ronanchilvers\Orm\Features\Relationship\HasOne;
use Ronanchilvers\Utility\Str;

/**
 * Feature trait for model relations
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait HasRelationships
{
    use BelongsTo,
        HasOne,
        HasMany;

    /**
     * Get the relation for a given attribute
     *
     * @param string $attribute The attribute to check for a relationship
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getRelation($attribute)
    {
        $shortAttribute = static::unprefix($attribute);
        $method = 'relate' . Str::pascal($shortAttribute);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }
}
