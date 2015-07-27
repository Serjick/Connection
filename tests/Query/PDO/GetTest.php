<?php

namespace Imhonet\Connection\Test\Query\PDO;

use Imhonet\Connection\Query\PDO\Get;
use Imhonet\Connection\Resource\IResource;

class GetTest extends \PHPUnit_Framework_TestCase
{
    private $statement = '
        SELECT user_id, name
        FROM user
        WHERE user_id IN (%s)
          AND gender = %s
        ORDER BY FIELD(user_id, %s)
        LIMIT %s, %s
    ';

    private $filter_ids = array(15, 2);
    private $filter_gender = 'f';
    private $filter_offset = 10;
    private $filter_limit = 10;

    /**
     * @var Get
     */
    private $query;

    protected function setUp()
    {
        $this->query = new Get();
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
            ->addParams(
                $this->filter_ids,
                $this->filter_gender,
                $this->filter_ids,
                $this->filter_offset,
                $this->filter_offset + $this->filter_limit
            )
        ;
        $this->assertInstanceOf('\\PDOStatement', $this->query->execute());
    }

    public function testErrorCode()
    {
        $this->query
            ->setResource($this->getResource())
            ->addStatement($this->statement)
            ->addParams(
                $this->filter_ids,
                $this->filter_gender,
                $this->filter_ids,
                $this->filter_offset,
                $this->filter_offset + $this->filter_limit
            )
        ;
        $this->assertEquals(0, $this->query->getErrorCode());
    }

    public function testFailure()
    {
        $this->query
            ->setResource($this->getResourceFailed())
            ->addStatement($this->statement)
            ->addParams(
                $this->filter_ids,
                $this->filter_gender,
                $this->filter_ids,
                $this->filter_offset,
                $this->filter_offset + $this->filter_limit
            )
        ;
        $this->assertInstanceOf('\\PDOStatement', $this->query->execute());
    }

    public function testFailureErrorCode()
    {
        $this->query
            ->setResource($this->getResourceFailed())
            ->addStatement($this->statement)
            ->addParams(
                $this->filter_ids,
                $this->filter_gender,
                $this->filter_ids,
                $this->filter_offset,
                $this->filter_offset + $this->filter_limit
            )
        ;
        $this->assertNotEquals(0, $this->query->getErrorCode());
    }

    /**
     * @return IResource
     */
    private function getResource()
    {
        $stmt = $this->getMock('\\PDOStatement', array('bindValue', 'execute'));
        $stmt
            ->expects($this->at(0))
            ->method('bindValue')
            ->with(1, $this->filter_ids[0], \PDO::PARAM_INT)
            ->will($this->returnValue(true))
        ;
        $stmt
            ->expects($this->at(1))
            ->method('bindValue')
            ->with(2, $this->filter_ids[1], \PDO::PARAM_INT)
            ->will($this->returnValue(true))
        ;
        $stmt
            ->expects($this->at(2))
            ->method('bindValue')
            ->with(3, $this->filter_gender, \PDO::PARAM_STR)
            ->will($this->returnValue(true))
        ;
        $stmt
            ->expects($this->at(3))
            ->method('bindValue')
            ->with(4, $this->filter_ids[0], \PDO::PARAM_INT)
            ->will($this->returnValue(true))
        ;
        $stmt
            ->expects($this->at(4))
            ->method('bindValue')
            ->with(5, $this->filter_ids[1], \PDO::PARAM_INT)
            ->will($this->returnValue(true))
        ;
        $stmt
            ->expects($this->at(5))
            ->method('bindValue')
            ->with(6, $this->filter_offset, \PDO::PARAM_INT)
            ->will($this->returnValue(true))
        ;
        $stmt
            ->expects($this->at(6))
            ->method('bindValue')
            ->with(7, $this->filter_offset + $this->filter_limit, \PDO::PARAM_INT)
            ->will($this->returnValue(true))
        ;
        $stmt
            ->expects($this->at(7))
            ->method('execute')
            ->will($this->returnValue(true))
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
        return sprintf($this->statement, '?,?', '?', '?,?', '?', '?');
    }

    protected function tearDown()
    {
        $this->query = null;
    }
}
