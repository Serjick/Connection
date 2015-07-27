<?php

namespace Imhonet\Connection\Test\Query\Memcache;

use Imhonet\Connection\Query\Memcache\Get;
use Imhonet\Connection\Resource\IResource;

class GetTest extends \PHPUnit_Framework_TestCase
{
    private $data = array(
        'false' => false,
        'true' => true,
        'null' => null,
        'int' => 42,
        'string' => 'forty two',
        'arr' => array(42),
    );

    /**
     * @var Get
     */
    private $query;

    protected function setUp()
    {
        $this->query = new Get();
        $this->query->setKeys(array_keys($this->data));
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\Query\Query', $this->query);
    }

    public function testExecute()
    {
        $this->query->setResource($this->getResource());
        $this->assertEquals($this->data, $this->query->execute());
    }

    public function testCount()
    {
        $this->query->setResource($this->getResource());
        $this->assertEquals(sizeof($this->data), $this->query->getCount());
        $this->assertEquals(sizeof($this->data), $this->query->getCountTotal());
    }

    public function testErrorCode()
    {
        $this->query->setResource($this->getResource());
        $this->assertEquals(0, $this->query->getErrorCode());
    }

    public function testFailure()
    {
        $this->query->setResource($this->getResourceFailed());
        $this->assertEquals(false, $this->query->execute());
    }

    public function testFailureCount()
    {
        $this->query->setResource($this->getResourceFailed());
        $this->assertEquals(0, $this->query->getCount());
        $this->assertEquals(0, $this->query->getCountTotal());
    }

    public function testFailureErrorCode()
    {
        $this->query->setResource($this->getResourceFailed());
        $this->assertNotEquals(0, $this->query->getErrorCode());
    }

    /**
     * @return IResource
     */
    private function getResource()
    {
        $memcache = $this->getMock('\Memcache', array('get'));
        $memcache
            ->expects($this->any())
            ->method('get')
            ->with(array_keys($this->data))
            ->will($this->returnValue($this->data))
        ;

        $resource = $this->getMock('Imhonet\Connection\Resource\Memcache', array('getHandle'));
        $resource
            ->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($memcache))
        ;

        return $resource;
    }

    /**
     * @return IResource
     */
    private function getResourceFailed()
    {
        $memcache = $this->getMock('\Memcache', array('get'));
        $memcache
            ->expects($this->any())
            ->method('get')
            ->withAnyParameters()
            ->will($this->returnValue(false))
        ;

        $resource = $this->getMock('Imhonet\Connection\Resource\Memcache', array('getHandle'));
        $resource
            ->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($memcache))
        ;

        return $resource;
    }

    protected function tearDown()
    {
        $this->query = null;
    }
}
