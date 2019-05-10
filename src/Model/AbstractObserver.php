<?php

namespace Ronanchilvers\Db\Model;

use Ronanchilvers\Db\Model;

/**
 * Abstract observer that defines all hooks as empty
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class AbstractObserver implements ObserverInterface
{
    /**
     * {@inheritdoc}
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function loaded(Model $model)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function creating(Model $model)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function created(Model $model)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function updating(Model $model)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function updated(Model $model)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function saving(Model $model)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function saved(Model $model)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function deleting(Model $model)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Ronanchilvers\Db\Model $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function deleted(Model $model)
    {
        return true;
    }
}
