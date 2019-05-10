<?php

namespace Ronanchilvers\Db;

use DateTime;
use PDO;
use Ronanchilvers\Db\Model\ObserverInterface;
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
    static public $table;

    /**
     * @var string
     */
    static public $primaryKey;

    /**
     * An instance of PDO to use for database interactions
     *
     * @var \PDO
     */
    static private $pdo;

    /**
     * @var array
     */
    static protected $observers = [];

    /**
     * Magic call for static methods
     *
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function __callStatic($method, $args)
    {
        $builder = (new static)->newQueryBuilder();
        if (method_exists($builder, $method)) {
            return call_user_func_array([$builder, $method], $args);
        }

        throw new RuntimeException(
            sprintf(
                'Undefined method %s::%s()',
                get_called_class(),
                $method
            )
        );
    }

    /**
     * Set the PDO instance to use for models
     *
     * @param \PDO $pdo
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function setPdo(PDO $pdo)
    {
        static::$pdo = $pdo;
    }

    /**
     * Get the configured PDO instance
     *
     * @return \PDO
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static protected function pdo()
    {
        return self::$pdo;
    }

    /**
     * Register a callable as an observer for a particular model
     *
     * @param \Ronanchilvers\Db\Model\ObserverInterface $observer
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function observe(ObserverInterface $observer)
    {
        $class = get_called_class();
        if (!isset(static::$observers[$class])) {
            static::$observers[$class] = [];
        }
        static::$observers[$class][] = $observer;
    }

    /**
     * Notify observers of an event
     *
     * @param Model $model
     * @param string $event
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static protected function notifyObservers(
        Model $model,
        string $event
    )
    {
        $class = get_called_class();
        if (!isset(static::$observers[$class])) {
            return;
        }
        if (!is_array(static::$observers[$class]) || 0 == count(static::$observers[$class])) {
            return;
        }
        foreach (static::$observers[$class] as $observer) {
            if (false === $observer->$event($model)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @var string
     */
    protected $columnPrefix = '';

    /**
     * @var array
     */
    protected $datetimeColumns = [];

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct()
    {}

    // public function __call($method, $args)
    // {
    //     if (0 === strpos($method, 'get') || 0 === strpos($method, 'set')) {
    //         $attribute = mb_substr($method, 3);
    //         switch (substr($method, 0, 3)) {
    //             case 'set':
    //                 return $this->setData($attribute, $args[0]);

    //             case 'get':
    //                 return $this->getData($attribute);
    //         }
    //     }

    //     throw new RuntimeException(
    //         sprintf(
    //             'Undefined method %s::%s()',
    //             get_called_class(),
    //             $method
    //         )
    //     );
    // }

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
        $index = array_search(static::$primaryKey, $names);
        unset($names[$index]);
        $names = array_map(function (&$name) {
            return $this->unprefix($name);
        }, $names);

        return $names;
    }

    /**
     * Get the data array for this model
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getDataArray()
    {
        return $this->data;
    }

    /**
     * Set the model data from an array
     *
     * @param array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setFromArray(array $data)
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
     * Get a new query builder for this model
     *
     * @return Ronanchilvers\Db\QueryBuilder
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function newQueryBuilder()
    {
        return new QueryBuilder(
            static::pdo(),
            get_called_class()
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
        $data         = $this->data;
        $queryBuilder = $this->newQueryBuilder();
        if (false === static::notifyObservers($this, 'saving'))
        {
            return false;
        }
        if (true === isset($data[static::$primaryKey])) {
            if (false === static::notifyObservers($this, 'updating'))
            {
                return false;
            }
            // Update
            $query = $queryBuilder->update();
            $id = $data[static::$primaryKey];
            unset($data[static::$primaryKey]);
            $query
                ->set(
                    $this->data
                )
                ->where(
                    static::$primaryKey,
                    '=',
                    $id
                );

            $result = $query->execute();
            if (false === $result) {
                return false;
            }
            static::notifyObservers($this, 'updated');
            static::notifyObservers($this, 'saved');
            return true;
        } else {
            if (false === static::notifyObservers($this, 'creating'))
            {
                return false;
            }
            // Insert
            $query = $queryBuilder->insert();
            $data = $this->data;
            unset($data[static::$primaryKey]);
            $query->values(
                $data
            );
            if (true !== $query->execute()) {
                return false;
            }
            $this->data[static::$primaryKey] = static::pdo()->lastInsertId();

            static::notifyObservers($this, 'created');
            static::notifyObservers($this, 'saved');
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
    public function destroy()
    {
        if (!isset($this->data[static::$primaryKey]) || empty($this->data[static::$primaryKey])) {
            throw new RuntimeException(
                sprintf('Unable to delete model without primary key %s', static::$primaryKey)
            );
        }
        if (false === static::notifyObservers($this, 'deleting')) {
            return false;
        }
        $query = $this->newQueryBuilder()
            ->delete()
            ->where(
                static::$primaryKey,
                '=',
                $this->data[static::$primaryKey]
            )
            ;
        if (false === $query->execute()) {
            return false;
        }
        unset($this->data[static::$primaryKey]);
        static::notifyObservers($this, 'deleted');

        return true;
    }

    /**
     * Set a data attribute on this model
     *
     * @param string $attribute
     * @param mixed $value
     * @return static
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setData($attribute, $value)
    {
        $attribute = Str::snake($attribute);
        $attributePrefixed = $this->prefix($attribute);
        if (!isset($this->columns[$attributePrefixed])) {
            throw new RuntimeException(
                sprintf('Unknown field %s', $attributePrefixed)
            );
        }
        if (static::$primaryKey == $attributePrefixed) {
            throw new RuntimeException(
                sprintf('Invalid attempt to overwrite primary key column %s', $attributePrefixed)
            );
        }

        // Auto mutation
        $mutator = 'mutate' . Str::pascal($attribute);
        if (is_callable([$this, $mutator])) {
            $value = $this->$mutator($value);
        } else if (in_array($attributePrefixed, $this->datetimeColumns)) {
            if (!$value instanceof DateTime) {
                $value = date_create($value);
            }
            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            } else {
                $value = null;
            }
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
    protected function getData($attribute)
    {
        $attribute = Str::snake($attribute);
        $attributePrefixed = $this->prefix($attribute);
        if (isset($this->data[$attributePrefixed])) {
            $data = $this->data[$attributePrefixed];

            // Auto mutations
            $getter = 'get' . Str::pascal($attribute);
            if (is_callable([$this, $getter])) {
                return $this->$getter($data);
            } else if (in_array($attributePrefixed, $this->datetimeColumns)) {
                if (false === $data = date_create($data)) {
                    $data = null;
                }
            }

            return $data;
        }

        return null;
    }

    /**
     * Prefix a string with the configured field prefix
     *
     * @param  string $string
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function prefix($string)
    {
        $prefix = $this->columnPrefix;
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
    public function unprefix($string)
    {
        if (!empty($this->columnPrefix) && 0 === strpos($string, $this->columnPrefix)) {
            return substr($string, strlen($this->columnPrefix) + 1);
        }

        return $string;
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
}
