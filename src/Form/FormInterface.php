<?php

namespace Ronanchilvers\Orm\Form;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for form objects
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface FormInterface
{
    /**
     * Validate the form
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isValid(): bool;

    /**
     * Get the populated model for this form
     *
     * @return Ronanchilvers\Orm\Model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function model();

    /**
     * Get the error message for a given field
     *
     * @param string $attribute
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function error($attribute);

    /**
     * Check if a field has an error
     *
     * @param string $attribute
     * @return bool
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isError($attribute);
}
