<?php

namespace Ronanchilvers\Orm\Traits;

use Respect\Validation\Exceptions\NestedValidationException;
use RuntimeException;

/**
 * Trait that adds validation support to models
 *
 * NB: This trait expects the class to use the respect/validation library.
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait HasValidationTrait
{
    /**
     * Array of arrays of rule objects per field grouped by scenario
     *
     * @var array
     */
    protected $rules = [];

    /**
     * The errors found by the last validation run
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Setup method for creating and registering rules
     *
     * This method is intended to be overriden by sub classes to set up their
     * validation rules.
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setupValidation()
    {
    }

    /**
     * Add a rule to this model
     *
     * @param array $rules      An array of rule objects
     * @param string $scenario  The validation scenario these rules apply to
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function registerRules(array $rules, $scenario = 'default')
    {
        if (!isset($this->rules[$scenario])) {
            $this->rules[$scenario] = [];
        }
        $this->rules[$scenario] = $rules;
    }

    /**
     * Get the current error array for this model
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Does a given field have an error?
     *
     * @param string $field
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function hasError($field)
    {
        $field = static::prefix($field);

        return isset($this->errors[$field]);
    }

    /**
     * Add an error to the errors array
     *
     * @param string $field
     * @param string $message
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function addError(string $field, string $message)
    {
        $field = static::prefix($field);
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Get the error for a field
     *
     * @param string $field
     * @return string|null
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getError($field)
    {
        $field = static::prefix($field);
        if (isset($this->errors[$field])) {
            return $this->errors[$field];
        }

        return null;
    }

    /**
     * Validate this model with its current data
     *
     * @param string $scenario  The validation scenario to validate against
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function validate($scenario = 'default')
    {
        $this->setupValidation();
        if (!isset($this->rules[$scenario])) {
            throw new RuntimeException(
                sprintf('Unable to validate non-existent scenario %s', $scenario)
            );
        }
        $this->errors = [];
        $rules        = $this->rules[$scenario];
        foreach ($rules as $field => $validator) {
            $field = static::prefix($field);
            $value = null;
            if (isset($this->data[$field])) {
                $value = $this->data[$field];
            }
            $name = ucwords(str_replace('_', ' ', strtolower(static::unprefix($field))));
            try {
                $validator
                    ->setName($name)
                    ->assert($value);
            } catch (NestedValidationException $ex) {
                foreach ($ex->getMessages() as $message) {
                    $this->addError($field, $message);
                }
            }
        }

        return 0 == count($this->errors);
    }

    /**
     * Save this model
     *
     * This method either inserts or updates the model row based on the presence
     * of an ID. It will return false if the save fails.
     *
     * @param string $scenario  The validation scenario to validate against
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function saveWithValidation($scenario = 'default')
    {
        if (false === $this->beforeSave()) {
            return false;
        }
        if (true === $this->isLoaded()) {
            if (false === $this->beforeUpdate()){
                return false;
            }
            if (false === $this->validate()) {
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
            if (false === $this->validate($scenario)) {
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
}
