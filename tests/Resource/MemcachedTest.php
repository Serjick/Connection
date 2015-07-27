<?php

namespace Imhonet\Connection\Test\Resource;

use Imhonet\Connection\Resource\IResource;
use Imhonet\Connection\Resource\Memcached;

class MemcachedTest extends \PHPUnit_Framework_TestCase
{
    const HOST = 'localhost';
    const PORT = 11211;

    /**
     * @var IResource
     */
    private $resource;

    protected function setUp()
    {
        $this->resource = new Memcached();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\Resource\IResource', $this->resource);
    }

    public function testHandler()
    {
        $this->assertInstanceOf('\Memcached', $this->resource->getHandle());
    }

    public function testAddServer()
    {
        $this->resource
            ->setHost(self::HOST)
            ->setPort(self::PORT)
        ;
        $actual = $this->resource->getHandle()->getServerList();
        $this->assertCount(1, $actual);
        $this->assertEquals(self::HOST, $actual[0]['host']);
        $this->assertEquals(self::PORT, $actual[0]['port']);
    }

    public function testAddServers()
    {
        $this->resource
            ->setHost(self::HOST . Memcached::DELIMITER . self::HOST)
            ->setPort(self::PORT . Memcached::DELIMITER . self::PORT)
        ;
        $actual = $this->resource->getHandle()->getServerList();
        $this->assertCount(2, $actual);
        $this->assertEquals(self::HOST, $actual[0]['host']);
        $this->assertEquals(self::PORT, $actual[0]['port']);
        $this->assertEquals(self::HOST, $actual[1]['host']);
        $this->assertEquals(self::PORT, $actual[1]['port']);
    }

    public function testOption()
    {
        define('MEMCACHED_TCP_NODELAY', true);
        $this->assertEquals(1, $this->resource->getHandle()->getOption(\Memcached::OPT_TCP_NODELAY));
    }

    protected function tearDown()
    {
        $this->resource = null;
    }

}
