<?php

namespace Imhonet\Connection\Query\Memcached;

use Imhonet\Connection\Resource\IResource;

class GetTest extends \PHPUnit_Framework_TestCase
{
    const IGNORE_VERSION = '2.2.0';

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
        if (!$this->checkExtentionVersion()) {
            $this->markTestSkipped('MMC version with broken reflections detected (@see https://github.com/php-memcached-dev/php-memcached/issues/147)');
        }

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
        $this->assertEquals(0, $this->query->getErrorCode());
    }

    public function testCount()
    {
        $this->query->setResource($this->getResource());
        $this->assertEquals(sizeof($this->data), $this->query->getCount());
        $this->assertEquals(sizeof($this->data), $this->query->getCountTotal());
    }

    public function testFailure()
    {
        $this->query->setResource($this->getResourceFailed());
        $this->assertEquals(false, $this->query->execute());
        $this->assertNotEquals(0, $this->query->getErrorCode());
    }

    /**
     * @return IResource
     */
    private function getResource()
    {
        $memcached = $this->getMock('\Memcached', array('getMulti'));
        $memcached
            ->expects($this->any())
            ->method('getMulti')
            ->with(array_keys($this->data))
            ->will($this->returnValue($this->data))
        ;

        $resource = $this->getMock('Imhonet\Connection\Resource\Memcached', array('getHandle'));
        $resource
            ->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($memcached))
        ;

        return $resource;
    }

    /**
     * @return IResource
     */
    private function getResourceFailed()
    {
        $memcached = $this->getMock('\Memcached', array('getMulti', 'getResultCode'));
        $memcached
            ->expects($this->any())
            ->method('getMulti')
            ->withAnyParameters()
            ->will($this->returnValue(false))
        ;
        $memcached
            ->expects($this->any())
            ->method('getResultCode')
            ->will($this->returnValue(\Memcached::RES_FAILURE))
        ;

        $resource = $this->getMock('Imhonet\Connection\Resource\Memcached', array('getHandle'));
        $resource
            ->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($memcached))
        ;

        return $resource;
    }

    private function checkExtentionVersion()
    {
        return phpversion('memcached') != self::IGNORE_VERSION;
    }

    protected function tearDown()
    {
        $this->query = null;
    }
}
