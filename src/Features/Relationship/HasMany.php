<?php

namespace Ronanchilvers\Orm\Features\Relationship;

use ReflectionClass;
use Ronanchilvers\Orm\Orm;
use Ronanchilvers\Utility\Str;
use RuntimeException;

/**
 * Has Many relationship - one to many, implies possesion
 *
 * Eg: A user has many bookmarks, a forest has many trees
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait HasMany
{
    /**
     * Get the data for a 'belongs to' relationship
     *
     * @param string $modelClass The model class that we 'belong to'
     * @param string $attribute The local data attribute that identifies the 'belongs to' key
     * @param string $foreignAttribute The foreign data attribute that the local field references
     * @return \Ronanchilvers\Orm\Model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function hasMany(string $modelClass, string $foreignAttribute = null, string $attribute = 'id')
    {
        if (!class_exists($modelClass)) {
            throw new RuntimeException('Invalid model class in belongs_to definition');
        }
        if (is_null($foreignAttribute)) {
            $reflection = new ReflectionClass(get_called_class());
            $foreignAttribute = Str::snake($reflection->getShortName());
            $foreignAttribute = strtolower($foreignAttribute);
            $foreignAttribute = $modelClass::prefix($foreignAttribute);
        }
        $attribute = static::prefix($attribute);
        $finder = Orm::finder($modelClass);

        return $finder->select()
                      ->where($foreignAttribute, $this->getAttribute($attribute))
                      ->execute();
    }
}
