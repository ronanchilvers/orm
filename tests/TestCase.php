<?php

namespace Ronanchilvers\Db\Test;

use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case with utility methods
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class TestCase extends BaseTestCase
{
    /**
     * Get a mock PDO instance
     *
     * @return \PDO
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockPDO()
    {
        return $this->createMock(PDO::class);
    }
}
