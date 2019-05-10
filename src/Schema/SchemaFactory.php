<?php

namespace Ronanchilvers\Db\Schema;

use Aura\SqlSchema\ColumnFactory;
use InvalidArgumentException;
use PDO;

/**
 *
 * A factory for schema objects.
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 * @see https://github.com/auraphp/Aura.SqlSchema/pull/18
 */
class SchemaFactory
{
    /**
     * @var \Aura\SqlSchema\ColumnFactory
     */
    protected $columnFactory;

    /**
     * @var array
     */
    protected $schemas = [
        'mysql'  => '\Aura\SqlSchema\MysqlSchema',
        'pgsql'  => '\Aura\SqlSchema\PgsqlSchema',
        'sqlite' => '\Aura\SqlSchema\SqliteSchema',
        'sqlsrv' => '\Aura\SqlSchema\SqlsrvSchema',
    ];

    /**
     * Class constructor
     *
     * @param \Aura\SqlSchema\ColumnFactory $columnFactory
     * @param array $schemas Map of names to schema classes
     */
    public function __construct(ColumnFactory $columnFactory = null, array $schemas = null)
    {
        $this->columnFactory = $columnFactory ?: new ColumnFactory;
        if (is_array($schemas)) {
            $this->schemas = $schemas;
        }
    }

    /**
     * Get the registered column factory
     *
     * @return \Aura\SqlSchema\ColumnFactory
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function columnFactory()
    {
        return $this->columnFactory;
    }

    /**
     * Get the registered schemas
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function schemas()
    {
        return $this->schemas;
    }

    /**
     * Get a new schema object
     *
     * @param \PDO    $pdo  A database connection
     * @param string $type type of schema to create
     * @return SchemaInterface
     */
    public function factory(PDO $pdo, $type = null)
    {
        $type = !is_null($type) ?: $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $type = strtolower($type);

        if (! isset($this->schemas[$type])) {
            throw new InvalidArgumentException(
                "No class for '$type' schema"
            );
        }

        $class = $this->schemas[$type];

        return new $class(
            $pdo,
            $this->columnFactory
        );
    }
}
