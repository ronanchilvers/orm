<?php

namespace Ronanchilvers\Orm\Features;

use Carbon\Carbon;


/**
 * Trait for managing timestamps on a model
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait HasTimestamps
{
    /**
     * @var boolean
     */
    static protected $usesTimestamps = true;

    /**
     * @var string
     */
    static protected $created = 'created';

    /**
     * @var string
     */
    static protected $updated = 'updated';

    /**
     * Boot the timestamps for this model
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function bootHasTimestamps()
    {
        $this->addType('datetime', static::$created);
        $this->addType('datetime', static::$updated);
    }

    /**
     * Does this model use timestamps?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function useTimestamps()
    {
        return static::$usesTimestamps;
    }

    /**
     * Update the timestamps for this model
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function updateTimestamps()
    {
        $now = Carbon::now();
        if (!$this->isLoaded() && !$this->isDirty(static::$created)) {
            $this->setAttribute(static::$created, $now);
        }
        if ($this->isLoaded() && !$this->isDirty(static::$updated)) {
            $this->setAttribute(static::$updated, $now);
        }
    }

    /**
     * Clear the timestamps on this model
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function clearTimestamps()
    {
        $this->setAttribute(static::$created, null);
        $this->setAttribute(static::$updated, null);
    }
}
