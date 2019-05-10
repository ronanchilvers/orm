<?php

namespace Ronanchilvers\Db\Model;

use Ronanchilvers\Db\Model;

/**
 * Interface for model observer objects
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface ObserverInterface
{
    /**
     * Fired after a model is loaded
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function loaded(Model $model);

    /**
     * Fired before a model is created
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function creating(Model $model);

    /**
     * Fired after a model is created
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function created(Model $model);

    /**
     * Fired before a model is updated
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function updating(Model $model);

    /**
     * Fired after a model is updated
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function updated(Model $model);

    /**
     * Fired before a model is saved (either created or updated)
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function saving(Model $model);

    /**
     * Fired after a model is saved (either created or updated)
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function saved(Model $model);

    /**
     * Fired before a model is deleted
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function deleting(Model $model);

    /**
     * Fired after a model is deleted
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function deleted(Model $model);
}
