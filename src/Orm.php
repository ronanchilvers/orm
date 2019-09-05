<?php

namespace Ronanchilvers\Orm;

use Evenement\EventEmitter;
use Exception;
use PDO;
use Ronanchilvers\Orm\Finder;

/**
 * Facade class that manages the db connection
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Orm
{
    /**
     * @var array
     */
    static protected $connection;

    /**
     * An event emitter instance
     *
     * @var Evenement\EventEmitter
     */
    static protected $emitter;

    /**
     * Set the PDO connection to use
     *
     * @param PDO $connection
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function setConnection(PDO $connection)
    {
        static::$connection = $connection;
    }

    /**
     * Get the current connection
     *
     * @return PDO
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function getConnection()
    {
        if (!static::$connection instanceof PDO) {
            throw new RuntimeException('No database connection configured');
        }
        return static::$connection;
    }

    /**
     * Get the event emitter object
     *
     * @return Evenement\EventEmitter
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getEmitter()
    {
        if (! static::$emitter instanceof EventEmitter) {
            static::$emitter = new EventEmitter();
        }

        return static::$emitter;
    }

    /**
     * Automated transaction handling
     *
     * @param Closure
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function transaction($closure)
    {
        $connection = static::$connection;
        try {
            $connection->beginTransaction();
            $result = $closure();

            if (false === $result) {
                $connection->rollback();
            } else {
                $connection->commit();
            }

            return $result;
        }
        catch (Exception $ex) {
            $connection->rollback();
            throw $ex;
        }
    }

    /**
     * Get a finder for a given model class
     *
     * @param string $modelClass
     * @return Ronanchilvers\Orm\Finder
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function finder($modelClass)
    {
        $finderClass = $modelClass::finder();
        if (false == $finderClass) {
            $finderClass = Finder::class;
        }

        return new $finderClass($modelClass);
    }
}
