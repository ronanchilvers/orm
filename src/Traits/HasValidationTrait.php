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
                $this->errors[$field] = $ex->getMessages();
            }
        }

        return 0 == count($this->errors);
    }
}
