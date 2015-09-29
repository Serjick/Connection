<?php

namespace Imhonet\Connection\Query\PDO;

use Imhonet\Connection\Resource\IResource;

class SetTest extends \PHPUnit_Framework_TestCase
{
    private $statement = 'INSERT INTO user SET name = %s';
    private $user_name = 'John Doe';

    /**
     * @var Set
     */
    private $query;

    protected function setUp()
    {
        $this->query = new Set();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\Query\Query', $this->query);
    }

    public function testExecute()
    {
        $this->query
            ->setResource($this->getResource())
            ->addStatement($this->statement)
            ->addParams($this->user_name)
        ;
        $this->assertInstanceOf('\\PDOStatement', $this->query->execute());
    }

    public function testCount()
    {
        $this->query
            ->setResource($this->getResource(true))
            ->addStatement($this->statement)
            ->addParams($this->user_name)
        ;
        $this->assertEquals(1, $this->query->getCount());
    }

    public function testLastId()
    {
        $this->query
            ->setResource($this->getResource())
            ->addStatement($this->statement)
            ->addParams($this->user_name)
        ;
        $this->assertEquals(5, $this->query->getLastId());
    }

    public function testErrorCode()
    {
        $this->query
            ->setResource($this->getResource())
            ->addStatement($this->statement)
            ->addParams($this->user_name)
        ;
        $this->assertEquals(0, $this->query->getErrorCode());
    }

    public function testFailure()
    {
        $this->query
            ->setResource($this->getResourceFailed())
            ->addStatement($this->statement)
            ->addParams($this->user_name)
        ;
        $this->assertInstanceOf('\\PDOStatement', $this->query->execute());
    }

    public function testFailureErrorCode()
    {
        $this->query
            ->setResource($this->getResourceFailed())
            ->addStatement($this->statement)
            ->addParams($this->user_name)
        ;
        $this->assertNotEquals(0, $this->query->getErrorCode());
    }

    /**
     * @param bool $mock_rowCount
     * @return IResource
     */
    private function getResource($mock_rowCount = false)
    {
        $stmt = $this->getMock('\\PDOStatement', array('bindValue', 'execute', 'rowCount'));
        $stmt
            ->expects($this->at(0))
            ->method('bindValue')
            ->with(1, $this->user_name, \PDO::PARAM_STR)
            ->will($this->returnValue(true))
        ;
        $stmt
            ->expects($this->at(1))
            ->method('execute')
            ->will($this->returnValue(true))
        ;
        if ($mock_rowCount) {
            $stmt
                ->expects($this->at(2))
                ->method('rowCount')
                ->will($this->returnValue(1))
            ;
        }

        $pdo = $this->getMock('\\PDO', array('prepare', 'lastInsertId'), array('sqlite::memory:'));
        $pdo
            ->expects($this->at(0))
            ->method('prepare')
            ->with($this->getPreparedStatement())
            ->will($this->returnValue($stmt))
        ;
        $pdo
            ->expects($this->at(1))
            ->method('lastInsertId')
            ->will($this->returnValue('5'))
        ;

        $resource = $this->getMock('Imhonet\Connection\Resource\PDO\MySQL', array('getHandle'));
        $resource
            ->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($pdo))
        ;

        return $resource;
    }

    /**
     * @return IResource
     */
    private function getResourceFailed()
    {
        $stmt = $this->getMock('\\PDOStatement', array('execute'));
        $stmt
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(false))
        ;

        $pdo = $this->getMock('\\PDO', array('prepare'), array('sqlite::memory:'));
        $pdo
            ->expects($this->at(0))
            ->method('prepare')
            ->with($this->getPreparedStatement())
            ->will($this->returnValue($stmt))
        ;

        $resource = $this->getMock('Imhonet\Connection\Resource\PDO\MySQL', array('getHandle'));
        $resource
            ->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($pdo))
        ;

        return $resource;
    }

    private function getPreparedStatement()
    {
        return sprintf($this->statement, '?');
    }

    protected function tearDown()
    {
        $this->query = null;
    }
}
