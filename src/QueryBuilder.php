<?php

namespace Ronanchilvers\Orm;

use ClanCats\Hydrahon\Builder;
use ClanCats\Hydrahon\Query\Sql\FetchableInterface;
use ClanCats\Hydrahon\Query\Sql\Insert;
use ClanCats\Hydrahon\Query\Sql\Select;
use ClanCats\Hydrahon\Query\Sql\Update;
use Closure;
use Exception;
use PDO;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\Model\Hydrator;
use RuntimeException;

/**
 * Class to build model queries and return model instances
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class QueryBuilder
{
    /**
     * @var \PDO
     */
    protected $connection;

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * Class constructor
     *
     * @param PDO $connection
     * @param string $model
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(
        PDO $connection,
        $modelClass
    ) {
        $this->connection = $connection;
        $this->modelClass = $modelClass;
    }

    /**
     * Get the connection object
     *
     * @return PDO
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get all records
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function all($page = null, $perPage = 10)
    {
        $select = $this->select();
        if (is_int($page)) {
            $select->page($page, $perPage);
        }

        return $select->execute();
    }

    /**
     * Get the first record in the table
     *
     * @return \Ronanchilvers\Orm\Model|null
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function first()
    {
        $modelClass = $this->modelClass;
        return $this
            ->select()
            ->first($modelClass::primaryKey());
    }

    /**
     * Get a single record by id
     *
     * @param mixed $id
     * @return \Ronanchilvers\Orm\Model|null
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function one($id)
    {
        $modelClass = $this->modelClass;

        return $this
            ->select()
            ->where($modelClass::primaryKey(), $id)
            ->one();
    }

    /**
     * Create a select object
     *
     * @return \ClanCats\Hydrahon\Query\Sql\Select
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function select()
    {
        $builder = $this->newBuilder();
        $modelClass = $this->modelClass;
        $select = $builder->select();
        $select
            ->table($modelClass::table());

        return $select;
    }

    /**
     * Query using a raw SQL statement
     *
     * @param string $sql
     * @param array $params
     * @param int $page
     * @param int $perPage
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function query($sql, $params = [], $page = null, $perPage = 20)
    {
        if (!is_null($page)) {
            $page   = (int) $page;
            if ($page < 1) {
                $page = 1;
            }
            $offset = $perPage * ($page - 1);
            $limit  = $perPage;

            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        $callback = $this->generateCallback();

        return $callback(
            null,
            $sql,
            $params
        );
    }

    /**
     * Create an insert query
     *
     * @return \ClanCats\Hydrahon\Query\Sql\Insert
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function insert()
    {
        $builder = $this->newBuilder();
        $modelClass = $this->modelClass;

        return $builder
            ->table($modelClass::table())
            ->insert();
    }

    /**
     * Create an update query
     *
     * @return \ClanCats\Hydrahon\Query\Sql\Update
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function update()
    {
        $builder = $this->newBuilder();
        $modelClass = $this->modelClass;

        return $builder
            ->table($modelClass::table())
            ->update();
    }

    /**
     * Get a delete query
     *
     * @return \ClanCats\Hydrahon\Query\Sql\Delete
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function delete()
    {
        $modelClass = $this->modelClass;

        return $this
            ->newBuilder()
            ->table($modelClass::table())
            ->delete();
    }

    /**
     * Create a hydrahon query builder
     *
     * @return \ClanCats\Hydrahon\Builder
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newBuilder()
    {
        // @todo Don't hardcode mysql
        return new \ClanCats\Hydrahon\Builder(
            'mysql',
            $this->generateCallback()
        );
    }

    /**
     * Generate a PDO callback
     *
     * @return \Closure
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function generateCallback()
    {
        return function ($query, $sql, $params) {
            $sql = trim($sql);
            Orm::getEmitter()->emit('query.init', [
                $sql,
                $params
            ]);
            $stmt = $this->connection->prepare(
                $sql
            );
            $params = array_map(function ($value) {
                if ($value instanceof Model) {
                    $value = $value->id;
                }
                return $value;
            }, $params);
            Orm::getEmitter()->emit('query.prepare', [
                $stmt,
                $sql,
                $params,
            ]);
            $result = $stmt->execute($params);
            if (false === $result) {
                Orm::getEmitter()->emit('query.fail', [
                    $stmt,
                    $sql,
                    $params
                ]);
                throw new RuntimeException(
                    implode(' : ', $stmt->errorInfo())
                );
            }
            if ('select' !== strtolower(substr($sql, 0, 6))) {
                return $result;
            }

            $class = $this->modelClass;
            $result = [];
            $hydrator = new Hydrator();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $model = new $class();
                $hydrator->hydrate($row, $model);
                $result[] = $model;
            }

            return $result;
        };
    }
}
