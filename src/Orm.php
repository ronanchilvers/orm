<?php

namespace Ronanchilvers\Orm;

use PDO;

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
}
