<?php

namespace Ronanchilvers\Db\Model;

use PDO;
use Ronanchilvers\Db\Model;
use Ronanchilvers\Db\Schema\SchemaFactory;
use Ronanchilvers\Utility\Str;

/**
 * Class responsible for providing model meta data such as table names
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Metadata
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var Ronanchilvers\Db\Schema\SchemaFactory
     */
    protected $schemaFactory = null;

    /**
     * @var array
     */
    protected $columns = null;

    /**
     * @var string
     */
    protected $primaryKey = null;

    /**
     * @var Ronanchilvers\Db\Model
     */
    protected $model = null;

    /**
     * @var string
     */
    protected $table = null;

    /**
     * @var string
     */
    protected $fieldPrefix = null;

    /**
     * Class constructor
     *
     * @param \PDO $pdo
     * @param \Ronanchilvers\Db\Model $model
     * @param \Ronanchilvers\Db\Schema\SchemaFactory $schemaFactory
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(
        PDO $pdo,
        Model $model,
        SchemaFactory $schemaFactory = null
    ) {
        $this->pdo = $pdo;
        $this->model = $model;
        $this->schemaFactory = $schemaFactory;
    }

    /**
     * Get the fully qualified class name for the model
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function fqcn()
    {
        return get_class($this->model);
    }

    /**
     * Get the model class for this configuration
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function class()
    {
        $class = get_class($this->model);
        $class = explode('\\', $class);

        return array_pop($class);
    }

    /**
     * Get the table name for the model
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function table()
    {
        if (is_null($this->table)) {
            $class = explode('\\', get_class($this->model));
            $class = array_pop($class);

            $this->table = $this->transformTableName(
                Str::snake($class)
            );
        }

        return $this->table;
    }

    /**
     * Get the column data for this model
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function columns()
    {
        if (is_null($this->columns)) {
            $class = $this->class();
            $schema = $this->schemaFactory()->factory(
                $this->pdo
            );
            $dbColumns = $schema->fetchTableCols(
                $this->table()
            );
            $columns = [];
            foreach ($dbColumns as $col) {
                $columns[$col->name] = [
                    'primary'=> $col->primary,
                    'type'   => $col->type,
                    'length' => $col->size,
                ];
            }
            $this->columns = $columns;
        }

        return $this->columns;
    }

    /**
     * Get the primary key column name
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function primaryKey()
    {
        if (is_null($this->primaryKey)) {
            foreach ($this->columns() as $column => $data) {
                if (true === $data['primary']) {
                    $this->primaryKey = $column;
                    break;
                }
            }
        }

        return $this->primaryKey;
    }

    /**
     * Check if a column exists
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function hasColumn($name)
    {
        $columns = $this->columns();

        return isset($columns[$name]);
    }

    /**
     * Prefix a string with the configured field prefix
     *
     * @param  string $string
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function prefix($string)
    {
        $prefix = $this->fieldPrefix;
        if (!empty($prefix)) {
            $prefix = "{$prefix}_";
        }
        if (0 === strpos($string, $prefix)) {
            return $string;
        }

        return "{$prefix}{$string}";
    }

    /**
     * Un-prefix a string with the configured field prefix
     *
     * @param string $string
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function unprefix($string)
    {
        $prefix = $this->fieldPrefix;
        if (0 === strpos($string, $prefix)) {
            return substr($string, strlen($prefix) + 1);
        }

        return $string;
    }

    /**
     * Transform a string into a table name
     *
     * @param string $string
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function transformTableName($string)
    {
        $string = strtolower($string);
        $string = preg_replace('#[^0-9A-z-_]+#', '', $string);
        $string = preg_replace('#[\s]+#', '_', $string);
        $string .= 's';

        return $string;
    }

    /**
     * Get a schema factory instance
     *
     * @codeCoverageIgnore
     * @return \Ronanchilvers\Db\Schema\SchemaFactory
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function schemaFactory()
    {
        if (!$this->schemaFactory instanceof SchemaFactory) {
            $this->schemaFactory = new SchemaFactory();
        }

        return $this->schemaFactory;
    }
}
