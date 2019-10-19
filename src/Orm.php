<?php

namespace Ronanchilvers\Orm;

use Closure;
use Evenement\EventEmitter;
use Exception;
use PDO;
use Ronanchilvers\Orm\Finder;
use RuntimeException;

/**
 * Facade class that manages the db connection
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Orm
{
    /**
     * @var \PDO|null
     */
    static protected $connection = null;

    /**
     * An event emitter instance
     *
     * @var \Evenement\EventEmitter|null
     */
    static protected $emitter = null;

    /**
     * Set the PDO connection to use
     *
     * @param \PDO $connection
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function setConnection(PDO $connection)
    {
        static::$connection = $connection;
    }

    /**
     * Get the current connection
     *
     * @return \PDO
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function getConnection()
    {
        if (is_null(static::$connection)) {
            throw new RuntimeException('No database connection configured');
        }
        return static::$connection;
    }

    /**
     * Get the event emitter object
     *
     * @return \Evenement\EventEmitter
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function getEmitter()
    {
        if (is_null(static::$emitter)) {
            static::$emitter = new EventEmitter();
        }

        return static::$emitter;
    }

    /**
     * Automated transaction handling
     *
     * @param \Closure
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function transaction(Closure $closure)
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
        } catch (Exception $ex) {
            $connection->rollback();
            throw $ex;
        }
    }

    /**
     * Get a finder for a given model class
     *
     * @param string $modelClass
     * @return \Ronanchilvers\Orm\Finder
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    static public function finder($modelClass)
    {
        $finderClass = $modelClass::finder();
        if (empty($finderClass)) {
            $finderClass = Finder::class;
        }

        return new $finderClass($modelClass);
    }
}
