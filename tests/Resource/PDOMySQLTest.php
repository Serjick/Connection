<?php

namespace Imhonet\Connection\Test\Resource;

use Imhonet\Connection\Resource\IResource;

/**
 * Class PDOMySQLTest
 * @covers Imhonet\Connection\Resource\PDO\MySQL::
 * @coversDefaultClass Imhonet\Connection\Resource\PDO\MySQL
 * @package Imhonet\Connection\Test\Resource
 */
class PDOMySQLTest extends \PHPUnit_Framework_TestCase
{
    const HOST = 'localhost';
    const PORT = 3306;
    const USER = 'root';
    const PASSWORD = '';
    const DB = 'test';

    private $resource;

    protected function setUp()
    {
    }

    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $connect
     * @return IResource
     */
    private function getResource(\PHPUnit_Framework_MockObject_Matcher_InvokedCount $connect)
    {
        if (!$this->resource) {
            $pdo = $this->getMock('\\PDO', null, array('sqlite::memory:'));

            $this->resource = $this->getMock('Imhonet\Connection\Resource\PDO\MySQL', array('connect'));
            $this->resource
                ->expects($connect)
                ->method('connect')
                ->will($this->returnValue($pdo))
            ;
        }

        return $this->resource;
    }

    /**
     * @covers ::__construct
     */
    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\Resource\IResource', $this->getResource($this->never()));
    }

    /**
     * @covers ::getHandle
     */
    public function testHandler()
    {
        $this->assertInstanceOf('\\PDO', $this->getResource($this->once())->getHandle());
    }

    public function testReuse()
    {
        $resource = $this->getResource($this->once());

        $resource->getHandle();
        $resource->getHandle();
    }

    protected function tearDown()
    {
        $this->resource = null;
    }
}
