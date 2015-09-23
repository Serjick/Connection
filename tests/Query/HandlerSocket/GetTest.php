<?php

namespace Imhonet\Connection\Query\HandlerSocket;

class GetTest extends \PHPUnit_Framework_TestCase
{
    const DB = 'users';
    const TABLE = 'user';
    const FIELD = 'login';
    const USER1_ID = 5;
    const USER2_ID = 10;

    private $data = [
        self::USER1_ID => array(self::FIELD => 'user1_login'),
        self::USER2_ID => array(self::FIELD => 'user2_login'),
    ];

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
        $this->assertInstanceOf('\\Imhonet\\Connection\\Query\\IQuery', $this->query);
    }

    public function testFormat()
    {
        $this->query
            ->setTable(self::TABLE)
            ->setIds([5, 10])
            ->setFields([self::FIELD])
            ->setResource($this->getResource());
        $this->assertInternalType('array', $this->query->execute());
    }

    public function testData()
    {
        $this->query
            ->setTable(self::TABLE)
            ->setIds([5, 10])
            ->setFields([self::FIELD])
            ->setResource($this->getResource());

        foreach ($this->query->execute() as $i => $row) {
            switch ($i) {
                case 0: $user_id = self::USER1_ID; break;
                case 1: $user_id = self::USER2_ID; break;
                default: $user_id = null;
            }

            $this->assertEquals([$this->data[$user_id][self::FIELD]], $row);
        }
    }

    public function testCounts()
    {
        $this->query
            ->setTable(self::TABLE)
            ->setIds([5, 10])
            ->setFields([self::FIELD])
            ->setResource($this->getResource());

        $this->assertEquals(sizeof($this->data), $this->query->getCount());
        $this->assertEquals(sizeof($this->data), $this->query->getCountTotal());
    }

    public function testFailure()
    {
        $this->query
            ->setTable(self::TABLE)
            ->setIds([5, 10])
            ->setFields([self::FIELD])
            ->setResource($this->getResourceFailed());

        $this->assertNull($this->query->execute());
    }

    public function testErrorCode()
    {
        $this->query
            ->setTable(self::TABLE)
            ->setIds([5, 10])
            ->setFields([self::FIELD])
            ->setResource($this->getResourceFailed());

        $this->assertEquals(1, $this->query->getErrorCode());
    }

    /**
     * @return \Imhonet\Connection\Resource\IResource
     */
    private function getResource()
    {
        $index_id = 1;

        $handle = $this->getMockBuilder('\\HSPHP\\ReadSocket')
            ->setMethods(['getIndexId', 'select', 'readResponse'])
            //->disableOriginalConstructor()
            ->getMock();
        $handle->expects($this->at(0))
            ->method('getIndexId')
            ->with(self::DB, self::TABLE, null, self::FIELD)
            ->will($this->returnValue($index_id));
        $handle->expects($this->at(1))
            ->method('select')
            ->with($index_id, '=', [0], 0, 0, [self::USER1_ID, self::USER2_ID])
            ->will($this->returnValue($index_id));
        $handle->expects($this->at(2))
            ->method('readResponse')
            ->will($this->returnValue([
                [$this->data[self::USER1_ID][self::FIELD]],
                [$this->data[self::USER2_ID][self::FIELD]]
            ]));

        $resource = $this->getMock('\\Imhonet\\Connection\\Resource\\HandlerSocket', array('getHandle', 'getDatabase'));
        $resource->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($handle));
        $resource->expects($this->any())
            ->method('getDatabase')
            ->will($this->returnValue(self::DB));

        return $resource;
    }

    /**
     * @return \Imhonet\Connection\Resource\IResource
     */
    private function getResourceFailed()
    {
        $handle = $this->getMockBuilder('\\HSPHP\\ReadSocket')
            ->setMethods(['getIndexId'])
            //->disableOriginalConstructor()
            ->getMock();
        $handle->expects($this->once())
            ->method('getIndexId')
            ->will($this->throwException(new \HSPHP\ErrorMessage));

        $resource = $this->getMock('\\Imhonet\\Connection\\Resource\\HandlerSocket', array('getHandle', 'getDatabase'));
        $resource->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($handle));
        $resource->expects($this->any())
            ->method('getDatabase')
            ->will($this->returnValue(self::DB));

        return $resource;
    }

    protected function tearDown()
    {
        $this->query = null;
    }
}
