<?php

namespace Ronanchilvers\Orm\Form;

use Psr\Http\Message\ServerRequestInterface;
use Ronanchilvers\Orm\Model;
use Valitron\Validator;

/**
 * Abstract base class for form objects
 *
 * This abstract expects to work with a data array of key/value pairs
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
 class AbstractForm implements FormInterface
 {
    /**
     * @var Ronanchilvers\Orm\Model
     */
    private $model;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * Class constructor
     *
     * @param Ronanchilvers\Orm\Model $model The model to use
     * @param array $fields Fields to validate
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(Model $model, array $fields)
    {
        $this->model = $model;
        $this->fields = array_combine(array_keys($fields), $fields);
        $this->errors = [];
    }

    /**
     * Magic call to pass method calls to the wrapped model
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __call($method, $args)
    {
        if (method_exists($this->model, $method)) {
            return call_user_func_array([$this->model, $method], $args);
        }
    }

    /**
     * Magic getter to proxy to the model
     *
     * @param string $attribute
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __get($attribute)
    {
        if (isset($this->data[$attribute])) {
            return $this->data[$attribute];
        }

        return $this->model->$attribute;
    }

    /**
     * Magic isset to check for properties
     *
     * @param string $attribute
     * @return bool
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __isset($attribute)
    {
        if (isset($this->data[$attribute])) {
            return true;
        }

        return $this->model->__isset($attribute);
    }

    /**
     * Set the data for this form
     *
     * @param array $data
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setData($data)
    {
        $fields = array_combine($this->fields, $this->fields);
        $data   = array_intersect_key(
            $data,
            $fields
        );
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $fields Fields that we are interested in
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isValid(): bool
    {
        $rules = $this->configure();
        if (empty($rules)) {
            return true;
        }

        // Validate the data
        $data = array_merge(
            $this->model->getDataArray(),
            $this->data
        );
        $validator = new Validator($data);
        $validator->rules($rules);
        $this->beforeValidate($validator);
        if ($validator->validate()) {
            return true;
        }
        $this->errors = $validator->errors();

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $attribute
     * @return bool
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isError($attribute)
    {
        return isset($this->errors[$attribute]);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $attribute
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function error($attribute)
    {
        if (!isset($this->errors[$attribute])) {
            return '';
        }
        return implode(', ', $this->errors[$attribute]);
    }

    /**
     * {@inheritdoc}
     *
     * @return Ronanchilvers\Orm\Model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function model(): Model
    {
        $data = $this->getModelData();
        if (!empty($data)) {
            $this->model->setFromArray($data);
        }

        return $this->model;
    }

    /**
     * Get the data for this form
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function data(): array
    {
        return $this->getModelData();
    }

    /**
     * Before validate hook
     *
     * @param Valitron\Validator $validator
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function beforeValidate(Validator $validator)
    {}

    /**
     * Get the model data for this form
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getModelData(): array
    {
        return $this->data;
    }

    /**
     * Configure validation rules
     *
     * This method MUST return an array of rules
     *
     * @param array $fields
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function configure(): array
    {
        $rules = [];
        foreach ($this->rules as $ruleName => $ruleFields) {
            $rule = [];
            foreach ($ruleFields as $fieldRule) {
                $fieldName = (is_array($fieldRule)) ? $fieldRule[0] : $fieldRule;
                if (0 == count($this->fields) || in_array($fieldName, $this->fields)) {
                    $rule[] = $fieldRule;
                }
            }
            if (0 < count($rule)) {
                $rules[$ruleName] = $rule;
            }
        }

        return $rules;
    }
 }
