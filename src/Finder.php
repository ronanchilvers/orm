<?php

namespace Ronanchilvers\Orm;

use Ronanchilvers\Orm\Orm;
use Ronanchilvers\Orm\QueryBuilder;
use RuntimeException;

/**
 * Base finder class for retrieving entities
 *
 * @method \PDO getConnection()
 * @method array all(int $page = null, int $perPage = 10)
 * @method \Ronanchilvers\Orm\Model|null first()
 * @method \Ronanchilvers\Orm\Model|null one(int $id)
 * @method \ClanCats\Hydrahon\Query\Sql\Select select()
 * @method mixed query(string $sql, array $params = [], int $page = null, int $perPage = 20)
 * @method \ClanCats\Hydrahon\Query\Sql\Insert insert()
 * @method \ClanCats\Hydrahon\Query\Sql\Update update()
 * @method \ClanCats\Hydrahon\Query\Sql\Delete delete()
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Finder
{
    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var string
     */
    protected $decoratorClass;

    /**
     * Class constructor
     *
     * @param string $modelClass
     * @param string $decoratorClass
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(
        string $modelClass,
        string $decoratorClass = null
    ) {
        $this->modelClass = $modelClass;
        $this->decoratorClass = $decoratorClass;
    }

    /**
     * Magic call for instance methods
     *
     * This method maps method calls onto the query builder
     *
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __call($method, $args)
    {
        $builder = $this->newQueryBuilder();
        if (method_exists($builder, $method)) {
            return call_user_func_array([$builder, $method], $args);
        }

        throw new RuntimeException(
            sprintf(
                'Undefined method %s::%s()',
                get_called_class(),
                $method
            )
        );
    }

    /**
     * Get a new query builder for this finder
     *
     * @return \Ronanchilvers\Orm\QueryBuilder
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newQueryBuilder()
    {
        return new QueryBuilder(
            Orm::getConnection(),
            $this->modelClass,
            $this->decoratorClass
        );
    }
}
