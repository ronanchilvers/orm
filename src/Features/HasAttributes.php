<?php

namespace Ronanchilvers\Orm\Features;

use Carbon\Carbon;
use Ronanchilvers\Utility\Str;

/**
 * Feature trait for handling attributes
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait HasAttributes
{
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

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /************************************/
    /* Attribute getters / setters ******/

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
            return $this->getAttributeTyped(
                $attribute,
                $value
            );
        }

        return $value;
    }

    /**
     * Get a data attribute for this model
     *
     * @param string $attribute
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    // public function getData($attribute)
    // {
    //     $attribute = Str::snake($attribute);
    //     $attributePrefixed = $this->prefix($attribute);
    //     if (isset($this->data[$attributePrefixed])) {
    //         $data = $this->data[$attributePrefixed];

    //         // Auto mutations
    //         // There's a custom getter
    //         $getter = 'get' . Str::pascal($attribute) . 'Attribute';
    //         if (is_callable([$this, $getter])) {
    //             return $this->$getter($data);

    //         // @todo Not yet handling models coming out

    //         // The value is a known timestamp - convert to Carbon
    //         } else if (
    //             in_array($attribute, static::$datetimes) ||
    //             $attribute == static::$created ||
    //             $attribute == static::$updated
    //         ) {
    //             try {
    //                 $carbon = new Carbon($data);
    //                 $data = $carbon;
    //             } catch (Exception $ex) { }
    //         }

    //         return $data;
    //     }

    //     return null;
    // }

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
            $value = $this->setAttributeTyped(
                $attribute,
                $value
            );
        }
        $this->data[static::prefix($attribute)] = $value;

        return $this;
    }

    /**
     * Set a data attribute on this model
     *
     * @param string $attribute
     * @param mixed $value
     * @return static
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    // public function setData($attribute, $value)
    // {
    //     $attribute = Str::snake($attribute);
    //     $attributePrefixed = $this->prefix($attribute);
    //     if (static::primaryKey() == $attributePrefixed) {
    //         throw new RuntimeException(
    //             sprintf('Invalid attempt to overwrite primary key column %s', $attributePrefixed)
    //         );
    //     }

    //     // Auto mutation
    //     // There's a custom setter
    //     $setter = 'set' . Str::pascal($attribute) . 'Attribute';
    //     if (is_callable([$this, $setter])) {
    //         $value = $this->$setter($value);

    //     // The value is a model - convert to an id
    //     } else if ($value instanceof self) {
    //         $value = $value->id;

    //     // The value is in the timestamps array - convert to a timestamp
    //     } else if (
    //         in_array($attribute, static::$datetimes) ||
    //         $attribute == static::$created ||
    //         $attribute == static::$updated
    //     ) {
    //         if (!empty($value) && !$value instanceof Carbon) {
    //             try {
    //                 $value = new Carbon($value);
    //             } catch (Exception $ex) {
    //                 $value = null;
    //             }
    //         }
    //         if ($value instanceof Carbon) {
    //             $value = $value->format('Y-m-d H:i:s');
    //         } else {
    //             $value = null;
    //         }
    //     }

    //     // Are we undoing a previous change?
    //     if (isset($this->oldData[$attributePrefixed]) &&
    //         $value === $this->oldData[$attributePrefixed]) {
    //         unset($this->oldData[$attributePrefixed]);

    //     // Keep a record of the old data
    //     } else {
    //         $this->oldData[$attributePrefixed] = $value;
    //     }
    //     $this->data[$attributePrefixed] = $value;

    //     return $this;
    // }

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
     * Does a given attribute have a specified type?
     *
     * @param string $attribute
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function hasType($attribute)
    {
        return isset($this->types[$attribute]);
    }

    /**
     * Get the typed value for a given attribute
     *
     * @param string $attribute
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getAttributeTyped(
        $attribute,
        $value
    ) {
        if (is_null($value)) {
            return $value;
        }

        switch ($this->types[$attribute]) {

            case 'datetime':
                return $this->toDateTime($value);
                break;

            default:
                return $value;

        }
    }

    /**
     * Set the typed value for a given attribute
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setAttributeTyped(
        $attribute,
        $value
    ) {
        if (is_null($value)) {
            return $value;
        }

        switch ($this->types[$attribute]) {

            case 'datetime':
                return $this->fromDateTime($value);
                break;

            default:
                return $value;

        }
    }

    /* Type handling ********************/
    /************************************/

    /************************************/
    /* Type transformers ****************/

    /**
     * Transform a value to a datetime object (Carbon)
     *
     * @param mixed $value
     * @return Carbon\Carbon
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function toDateTime($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        // Assume if the value is numeric that its a unix timestamp
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        return Carbon::createFromFormat(
            $this->dateFormat,
            $value
        );
    }

    /**
     * Transform a value from a datetime object (Carbon)
     *
     * @param Carbon\Carbon $datetime
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function fromDateTime($datetime)
    {
        if (!$datetime instanceof \DateTime) {
            return $datetime;
        }

        return $datetime->format(
            $this->dateFormat
        );
    }

    /* Type transformers ****************/
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
    public function toArray()
    {
        return $this->data;
    }

    /* Import / export methods **********/
    /************************************/
}
