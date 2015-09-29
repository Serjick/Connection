<?php

namespace Imhonet\Connection\Query\Memcache;

use Imhonet\Connection\Resource\IResource;

class SetTest extends \PHPUnit_Framework_TestCase
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
     * @var Set
     */
    private $query;

    protected function setUp()
    {
        $this->query = new Set();
        $this->query->setData($this->data);
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\Query\Query', $this->query);
    }

    public function testExecute()
    {
        $this->query->setResource($this->getResource());
        $this->assertEquals(true, $this->query->execute());
    }

    public function testCount()
    {
        $this->query->setResource($this->getResource());
        $this->assertEquals(sizeof($this->data), $this->query->getCount());
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
        $memcache = $this->getMock('\Memcache', array('set'));
        $memcache
            ->expects($this->any())
            ->method('set')
            ->withAnyParameters()
            ->will($this->returnValue(true))
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
        $map = array();

        foreach ($this->data as $key => $value) {
            $map[] = array($key, $value, $key != 'null');
        }

        $memcache = $this->getMock('\Memcache', array('set'));
        $memcache
            ->expects($this->any())
            ->method('set')
            ->will($this->returnValueMap($map))
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
