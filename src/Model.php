<?php

namespace Ronanchilvers\Orm;

use Carbon\Carbon;
use DateTime;
use Exception;
use Ronanchilvers\Orm\Features\HasAttributes;
use Ronanchilvers\Orm\Features\HasHooks;
use Ronanchilvers\Orm\Features\HasRelationships;
use Ronanchilvers\Orm\Features\HasTimestamps;
use Ronanchilvers\Orm\Orm;
use Ronanchilvers\Utility\Str;
use RuntimeException;
use Serializable;

/**
 * Base model class for all models
 *
 * @property int id
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class Model implements Serializable
{
    use HasHooks,
        HasAttributes,
        HasTimestamps,
        HasRelationships;

    /**
     * @var string
     */
    static protected $finder = '';

    /**
     * @var string
     */
    static protected $table = '';

    /**
     * @var string
     */
    static protected $columnPrefix = '';

    /**
     * @var string
     */
    static protected $primaryKey = '';

    /**
     * Get the finder class for this model
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function finder()
    {
        return static::$finder;
    }

    /**
     * Get the table name for this model
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function table()
    {
        if ('' === static::$table) {
            $reflection = new \ReflectionClass(get_called_class());
            $table = Str::plural(Str::snake($reflection->getShortName()), 2);

            return $table;
        }

        return static::$table;
    }

    /**
     * Get the primary key fieldname for this model
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function primaryKey()
    {
        if ('' === static::$primaryKey) {
            return static::prefix('id');
        }

        return static::$primaryKey;
    }

    /**
     * Prefix a string with the configured field prefix
     *
     * @param  string $string
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function prefix($string)
    {
        $prefix = static::$columnPrefix;
        if (!empty($prefix)) {
            $prefix = "{$prefix}_";
        }
        if (!empty($prefix) && 0 === strpos($string, $prefix)) {
            return $string;
        }

        return "{$prefix}{$string}";
    }

    /**
     * Un-prefix a string with the configured field prefix
     *
     * @param string $string
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function unprefix($string)
    {
        if (!empty(static::$columnPrefix) && 0 === strpos($string, static::$columnPrefix)) {
            return substr($string, strlen(static::$columnPrefix) + 1);
        }

        return $string;
    }

    /**
     * Transform a string into a fully qualified column with table and prefix
     *
     * @param string $string
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public static function qualify($string)
    {
        if ('*' != $string) {
            $string = static::prefix($string);
        }
        $table = static::table();

        return "{$table}.{$string}";
    }

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct()
    {
        if ($this->useTimestamps()) {
            $this->bootHasTimestamps();
        }
        $this->boot();
    }

    /**
     * Magic clone method to ensure that cloned models are new
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __clone()
    {
        $primaryKey = static::primaryKey();
        if (isset($this->data[$primaryKey])) {
            unset($this->data[$primaryKey]);
        }
        if ($this->useTimestamps()) {
            $this->clearTimestamps();
        }
        $this->clone();
    }

    /**
     * Clone function designed to be overriden by subclasses
     *
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function clone()
    {}

    /**
     * Boot the model
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function boot()
    {}

    /**
     * Magic property isset
     *
     * @param string $attribute
     * @return bool
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __isset($attribute)
    {
        return $this->hasAttribute($attribute);
    }

    /**
     * General property getter
     *
     * @param string $attribute
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function get($attribute)
    {
        $result = $this->getRelation($attribute);
        if (!is_null($result)) {
            return $result;
        }
        return $this->getAttribute($attribute);
    }

    /**
     * Magic property getter
     *
     * @param string $attribute
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __get($attribute)
    {
        return $this->get($attribute);
    }

    /**
     * General property setter
     *
     * @param string $attribute
     * @param mixed $value
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function set($attribute, $value)
    {
        return $this->setAttribute($attribute, $value);
    }

    /**
     * Magic property setter
     *
     * @param string $attribute
     * @param mixed $value
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __set($attribute, $value)
    {
        return $this->set($attribute, $value);
    }

    /**
     * Serialize the data array
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function serialize()
    {
        return serialize($this->toArray());
    }

    /**
     * Unserialize the data array
     *
     * @param string $serialized
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function unserialize($serialized)
    {
        $this->fromArray(unserialize($serialized));
        $this->boot();
    }

    /* Persistance methods **************/
    /************************************/

    /**
     * Is this model loaded?
     *
     * This simply means, do we have a primary key id?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isLoaded()
    {
        $key = static::primaryKey();
        return (
            $this->hasAttribute($key) &&
            is_numeric($this->getAttribute($key))
        );
    }

    /**
     * Save this model
     *
     * This method either inserts or updates the model row based on the presence
     * of an ID. It will return false if the save fails.
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function save()
    {
        if (false === $this->beforeSave()) {
            return false;
        }
        if (true === $this->isLoaded()) {
            if (false === $this->beforeUpdate()) {
                return false;
            }
            if ($this->useTimestamps()) {
                $this->updateTimestamps();
            }
            if (true !== $this->persistUpdate()) {
                return false;
            }
            $this->afterUpdate();
            $this->afterSave();
            return true;
        } else {
            if (false === $this->beforeCreate()) {
                return false;
            }
            if ($this->useTimestamps()) {
                $this->updateTimestamps();
            }
            if (true !== $this->persistInsert()) {
                return false;
            }
            $this->afterCreate();
            $this->afterSave();
            return true;
        }

        return false;
    }

    /**
     * Delete this model record
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function delete()
    {
        if (!$this->isLoaded()) {
            throw new RuntimeException(
                sprintf('Unable to delete model without primary key %s', static::primaryKey())
            );
        }
        if (false === $this->beforeDelete()) {
            return false;
        }
        if (false === $this->persistDelete()) {
            return false;
        }
        unset($this->data[static::primaryKey()]);
        $this->afterDelete();

        return true;
    }

    /**
     * Insert this model into the database
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function persistInsert()
    {
        $this->beforePersist();
        $queryBuilder = $this->getQueryBuilderInstance();
        $query        = $queryBuilder->insert();
        $data         = $this->getAttributes();
        unset($data[static::primaryKey()]);
        $query->values(
            $data
        );
        if (true !== $query->execute()) {
            return false;
        }
        $this->data[static::primaryKey()] = $queryBuilder->getConnection()->lastInsertId();
        $this->oldData = [];

        return true;
    }

    /**
     * Update this model in the database
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function persistUpdate()
    {
        $this->beforePersist();
        $queryBuilder = $this->getQueryBuilderInstance();
        $query        = $queryBuilder->update();
        $data         = $this->getAttributes();
        $id           = $data[static::primaryKey()];
        unset($data[static::primaryKey()]);
        $query
            ->set(
                $data
            )
            ->where(
                static::primaryKey(),
                '=',
                $id
            );
        $result = $query->execute();
        if (false == $result) {
            return false;
        }
        $this->oldData = [];

        return true;
    }

    /**
     * Delete this model from the database
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function persistDelete()
    {
        $queryBuilder = $this->getQueryBuilderInstance();
        $query = $queryBuilder
            ->delete()
            ->where(
                static::primaryKey(),
                '=',
                $this->data[static::primaryKey()]
            )
            ;
        if (false === $query->execute()) {
            return false;
        }
        unset($this->data[static::primaryKey()]);

        return true;
    }

    /**
     * Get a query builder for this model
     *
     * @return \Ronanchilvers\Orm\QueryBuilder
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getQueryBuilderInstance()
    {
        $connection = Orm::getConnection();

        return new QueryBuilder(
            $connection,
            get_called_class()
        );
    }

    /* Persistance methods **************/
    /************************************/
}
