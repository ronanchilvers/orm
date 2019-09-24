<?php

namespace Ronanchilvers\Orm\Features;

use Ronanchilvers\Orm\Features\Type\ArrayHandler;
use Ronanchilvers\Orm\Features\Type\DateTimeHandler;
use Ronanchilvers\Orm\Features\Type\HandlerInterface;
use Ronanchilvers\Utility\Str;

/**
 * Feature trait for handling attributes
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait HasAttributes
{
    /**
     * @var Ronanchilvers\Orm\Features\Type\HandlerInterface[]
     */
    static protected $typeHandlers = [
        'array'    => ArrayHandler::class,
        'datetime' => DateTimeHandler::class,
        'model'    => ModelHandler::class,
    ];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $oldData = [];

    /**
     * @var array
     */
    protected $types = [];

    /************************************/
    /* Attribute getters / setters ******/

    /**
     * Get all the attributes for this model
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getAttributes()
    {
        return $this->data;
    }

    /**
     * Does this model have a given attribute?
     *
     * @param string $attribute
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function hasAttribute($attribute)
    {
        $attribute = static::prefix(
            Str::snake($attribute)
        );

        return array_key_exists($attribute, $this->data);
    }

    /**
     * Get the value of a given attribute
     *
     * @param string $attribute
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getAttribute($attribute)
    {
        if (!$this->hasAttribute($attribute)) {
            return null;
        }
        $attribute = Str::snake($attribute);
        $value = $this->data[static::prefix($attribute)];

        // Handle mutators here
        if ($this->hasGetMutator($attribute)) {
            return $this->getAttributeMutated(
                $attribute,
                $value
            );
        }

        // Handle types here
        if ($this->hasType($attribute)) {
            return $this->getAttributeToType(
                $attribute,
                $value
            );
        }

        return $value;
    }

    /**
     * Get the raw value of a given attribute without any transformation
     *
     * @param string $attribute
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getAttributeRaw($attribute)
    {
        if (!$this->hasAttribute($attribute)) {
            return null;
        }
        $attribute = Str::snake($attribute);

        return $this->data[static::prefix($attribute)];
    }

    /**
     * Get the value of a given attribute
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setAttribute($attribute, $value)
    {
        $attribute = Str::snake($attribute);

        // Handle mutators here
        if ($this->hasSetMutator($attribute)) {
            $value = $this->setAttributeMutated(
                $attribute,
                $value
            );
        }

        // Handle types here
        if ($this->hasType($attribute)) {
            $value = $this->getAttributeToRaw(
                $attribute,
                $value
            );
        }

        $attributePrefixed = static::prefix($attribute);
        // Are we undoing a previous change?
        if (isset($this->oldData[$attributePrefixed]) &&
            $value === $this->oldData[$attributePrefixed]) {
            unset($this->oldData[$attributePrefixed]);

        // Keep a record of the old data
        } else {
            $oldValue = isset($this->data[$attributePrefixed]) ? $this->data[$attributePrefixed] : null;
            $this->oldData[$attributePrefixed] = $oldValue;
        }
        $this->data[$attributePrefixed] = $value;

        return $this;
    }

    /**
     * Is the model or a given field dirty?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isDirty($attribute = null)
    {
        if (is_null($attribute)) {
            return !empty($this->oldData);
        }
        $attribute = static::prefix($attribute);

        return isset($this->oldData[$attribute]);
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
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

    /* Attribute getters / setters ******/
    /************************************/

    /************************************/
    /* Type handling ********************/

    /**
     * Register a type handler class
     *
     * @param string $type
     * @param Ronanchilvers\Orm\Features\Type\HandlerInterface $handler
     * @return static
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function registerTypeHandler(string $type, HandlerInterface $handler)
    {
        self::$typeHandlers[$type] = $handler;
    }

    /**
     * Get a type handler object
     *
     * @return Ronanchilvers\Orm\Features\Type\HandlerInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getTypeHandler($type)
    {
        if (!isset(self::$typeHandlers[$type])) {
            return null;
        }
        $class = self::$typeHandlers[$type];

        return new $class;
    }

    /**
     * Add a type for a given attribute
     *
     * @param string $type
     * @param string $attribute
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function addType($type, $attribute)
    {
        $this->types[$attribute] = $type;
    }

    /**
     * Does a given attribute have a specified type?
     *
     * @param string $attribute
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function hasType($attribute)
    {
        if (!isset($this->types[$attribute])) {
            return false;
        }
        $type = $this->types[$attribute];
        if (!isset(self::$typeHandlers[$type])) {
            return false;
        }

        return true;
    }

    /**
     * Get the typed value for a given attribute
     *
     * @param string $attribute
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getAttributeToType(
        $attribute,
        $value
    ) {
        if (is_null($value)) {
            return $value;
        }
        $handler = self::getTypeHandler($this->types[$attribute]);

        return $handler->toType(
            $value
        );
    }

    /**
     * Set the typed value for a given attribute
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getAttributeToRaw(
        $attribute,
        $value
    ) {
        if (is_null($value)) {
            return $value;
        }
        $handler = self::getTypeHandler($this->types[$attribute]);

        return $handler->toRaw(
            $value
        );
    }

    /* Type handling ********************/
    /************************************/

    /************************************/
    /* Mutator handling *****************/

    /**
     * Does a given attribute have a getter mutator?
     *
     * @param string $attribute
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function hasGetMutator($attribute)
    {
        return method_exists(
            $this,
            'get' . Str::pascal($attribute) . 'Attribute'
        );
    }

    /**
     * Does a given attribute have a setter mutator?
     *
     * @param string $attribute
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function hasSetMutator($attribute)
    {
        return method_exists(
            $this,
            'set' . Str::pascal($attribute) . 'Attribute'
        );
    }

    /**
     * Get the mutated value for an attribute
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getAttributeMutated($attribute, $value)
    {
        return $this->{'get' . Str::pascal($attribute) . 'Attribute'}(
            $value
        );
    }

    /**
     * Set the mutated value for an attribute
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setAttributeMutated($attribute, $value)
    {
        return $this->{'get' . Str::pascal($attribute) . 'Attribute'}(
            $value
        );
    }

    /* Mutator handling *****************/
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
                $this->setAttribute($attribute, $value);
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
    public function toArray($unprefixKeys = true)
    {
        if (true == $unprefixKeys) {
            $data = [];
            foreach ($this->data as $key => $value) {
                $data[static::unprefix($key)] = $value;
            }
            return $data;
        }
        return $this->data;
    }

    /* Import / export methods **********/
    /************************************/
}
