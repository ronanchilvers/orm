<?php

namespace Ronanchilvers\Orm;

use Carbon\Carbon;
use DateTime;
use Exception;
use Ronanchilvers\Orm\Orm;
use Ronanchilvers\Utility\Str;
use RuntimeException;
use Serializable;

/**
 * Base model class for all models
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class Model implements Serializable
{
    /**
     * @var string
     */
    static protected $finder = false;

    /**
     * @var string
     */
    static protected $table = false;

    /**
     * @var string
     */
    static protected $columnPrefix = false;

    /**
     * @var string
     */
    static protected $primaryKey = false;

    /**
     * @var array
     */
    static protected $timestamps = [
        'created',
        'updated'
    ];

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
        if (false == static::$table) {
            return strtolower(
                Str::plural(get_called_class(), 2)
            );
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
        if (false == static::$primaryKey) {
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
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $oldData = [];

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct()
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
        $attribute = Str::snake(
            $this->prefix($attribute)
        );

        return array_key_exists($attribute, $this->data);
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
        return $this->getData($attribute);
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
        $this->setData($attribute, $value);
    }

    /**
     * Get additional data stored on the model that doesn't relate directly to a column
     *
     * This is to allow for cases where you load calculated data with a query and want to
     * pull it out of the model later.
     *
     * @param string $key
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getAdditional($key)
    {
        if (!isset($this->columns[$key]) && isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * Get the property names for this model
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getPropertyNames()
    {
        $names = array_keys($this->data);
        $index = array_search(static::primaryKey(), $names);
        unset($names[$index]);
        $names = array_map(function (&$name) {
            return $this->unprefix($name);
        }, $names);

        return $names;
    }

    /**
     * Set a data attribute on this model
     *
     * @param string $attribute
     * @param mixed $value
     * @return static
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setData($attribute, $value)
    {
        $attribute = Str::snake($attribute);
        $attributePrefixed = $this->prefix($attribute);
        if (static::primaryKey() == $attributePrefixed) {
            throw new RuntimeException(
                sprintf('Invalid attempt to overwrite primary key column %s', $attributePrefixed)
            );
        }

        // Auto mutation
        // There's a custom setter
        $setter = 'set' . Str::pascal($attribute) . 'Attribute';
        if (is_callable([$this, $setter])) {
            $value = $this->$setter($value);

        // The value is a model - convert to an id
        } else if ($value instanceof self) {
            $value = $value->id;

        // The value is in the timestamps array - convert to a timestamp
        } else if (in_array($attribute, static::$timestamps)) {
            if (!empty($value) && !$value instanceof Carbon) {
                try {
                    $value = new Carbon($value);
                } catch (Exception $ex) {
                    $value = null;
                }
            }
            if ($value instanceof Carbon) {
                $value = $value->format('Y-m-d H:i:s');
            } else {
                $value = null;
            }
        }

        // Are we undoing a previous change?
        if (isset($this->oldData[$attributePrefixed]) &&
            $value === $this->oldData[$attributePrefixed]) {
            unset($this->oldData[$attributePrefixed]);

        // Keep a record of the old data
        } else {
            $this->oldData[$attributePrefixed] = $value;
        }
        $this->data[$attributePrefixed] = $value;

        return $this;
    }

    /**
     * Get a data attribute for this model
     *
     * @param string $attribute
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getData($attribute)
    {
        $attribute = Str::snake($attribute);
        $attributePrefixed = $this->prefix($attribute);
        if (isset($this->data[$attributePrefixed])) {
            $data = $this->data[$attributePrefixed];

            // Auto mutations
            // There's a custom getter
            $getter = 'get' . Str::pascal($attribute) . 'Attribute';
            if (is_callable([$this, $getter])) {
                return $this->$getter($data);

            // @todo Not yet handling models coming out

            // The value is a known timestamp - convert to Carbon
            } else if (in_array($attribute, static::$timestamps)) {
                try {
                    $carbon = new Carbon($data);
                    $data = $carbon;
                } catch (Exception $ex) { }
            }

            return $data;
        }

        return null;
    }

    /**
     * Serialize the data array
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * Unserialize the data array
     *
     * @param string $serialized
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }

    /* Persistance methods **************/
    /************************************/

    /**
     * Is the model or a given field dirty?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isDirty($field = null)
    {
        if (is_null($field)) {
            return !empty($this->oldData);
        }
        $field = static::prefix($field);

        return isset($this->oldData[$field]);
    }

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
        if (isset($this->data[static::primaryKey()]) && is_numeric($this->data[static::primaryKey()])) {
            return true;
        }

        return false;
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
            if (false === $this->beforeUpdate()){
                return false;
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
        if (false === $query->persistDelete()) {
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
        $data         = $this->data;
        unset($data[static::primaryKey()]);
        $query->values(
            $data
        );
        if (true !== $query->execute()) {
            return false;
        }
        $this->data[static::primaryKey()] = $queryBuilder->getConnection()->lastInsertId();

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
        $data         = $this->data;
        $id           = $data[static::primaryKey()];
        unset($data[static::primaryKey()]);
        $query
            ->set(
                $this->data
            )
            ->where(
                static::primaryKey(),
                '=',
                $id
            );

        return $query->execute();
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
     * @return Ronanchilvers\Orm\QueryBuilder
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getQueryBuilderInstance()
    {
        $connection   = Orm::getConnection();

        return new QueryBuilder(
            $connection,
            get_called_class()
        );
    }

    /* Persistance methods **************/
    /************************************/

    /************************************/
    /* Import / export methods **********/

    /**
     * Set the model data from an array
     *
     * @param array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function fromArray(array $data)
    {
        foreach ($data as $attribute => $value) {
            try {
                $this->setData($attribute, $value);
            } catch (RuntimeException $ex) {
                continue;
            }
        }
    }

    /**
     * Get the model as an array
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toArray()
    {
        return $this->data;
    }

    /* Import / export methods **********/
    /************************************/

    /************************************/
    /** Model Hooks *********************/

    /**
     * This hook fires immediately before model data is persisted to the db
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function beforePersist()
    {}

    /**
     * Model hook for the 'loaded' event
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function afterLoad()
    {}

    /**
     * Model hook for the 'saving' event
     *
     * Returning boolean false from this method cancels the event
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function beforeSave()
    {}

    /**
     * Model hook for the 'saved' event
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function afterSave()
    {}

    /**
     * Model hook for the 'creating' event
     *
     * Returning boolean false from this method cancels the event
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function beforeCreate()
    {}

    /**
     * Model hook for the 'created' event
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function afterCreate()
    {}

    /**
     * Model hook for the 'updating' event
     *
     * Returning boolean false from this method cancels the event
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function beforeUpdate()
    {}

    /**
     * Model hook for the 'updated' event
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function afterUpdate()
    {}

    /**
     * Model hook for the 'deleting' event
     *
     * Returning boolean false from this method cancels the event
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function beforeDelete()
    {}

    /**
     * Model hook for the 'deleted' event
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function afterDelete()
    {}

    /** Model Hooks *********************/
    /************************************/
}
