<?php

namespace Ronanchilvers\Orm\Features\Relationship;

use ReflectionClass;
use Ronanchilvers\Orm\Orm;
use Ronanchilvers\Utility\Str;
use RuntimeException;

/**
 * Belongs To relationship - one to one, implies ownership
 *
 * Eg: A book belongs to an author, a wheel belongs to a bike
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait BelongsTo
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
    protected function belongsTo(string $modelClass, string $attribute = null, string $foreignAttribute = 'id')
    {
        if (!class_exists($modelClass)) {
            throw new RuntimeException('Invalid model class in belongs_to definition');
        }
        if (is_null($attribute)) {
            $reflection = new ReflectionClass($modelClass);
            $attribute = Str::snake($reflection->getShortName());
            $attribute = strtolower($attribute);
            $attribute = static::prefix($attribute);
        }
        $foreignAttribute = $modelClass::prefix($foreignAttribute);
        $finder = Orm::finder($modelClass);

        return $finder->select()
                      ->where($foreignAttribute, $this->getAttribute($attribute))
                      ->one();
    }
}
