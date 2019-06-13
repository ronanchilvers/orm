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
            $this->setData(static::$created, $now);
        }
        if ($this->isLoaded() && !$this->isDirty(static::$updated)) {
            $this->setData(static::$updated, $now);
        }
    }
}
