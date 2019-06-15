<?php

namespace Ronanchilvers\Orm\Features;

/**
 * Feature trait for model hooks
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait HasHooks
{
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
