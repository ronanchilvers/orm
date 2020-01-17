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
     * Does this model have a relationship for a given field?
     *
     * @param string $attribute
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function hasRelation($attribute)
    {
        $method = $this->getRelationMethod($attribute);

        return method_exists($this, $method);
    }

    /**
     * Get the relation for a given attribute
     *
     * @param string $attribute The attribute to check for a relationship
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getRelation($attribute)
    {
        $method = $this->getRelationMethod($attribute);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }

    /**
     * Create a relationship method for a given attribute name
     *
     * @param string
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getRelationMethod($attribute)
    {
        $shortAttribute = static::unprefix($attribute);

        return 'relate' . Str::pascal($shortAttribute);
    }
}
