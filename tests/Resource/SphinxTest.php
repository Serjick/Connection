<?php

namespace Imhonet\Connection\Resource;

/**
 * Class SphinxTest
 * @covers Imhonet\Connection\Resource\Sphinx::
 * @coversDefaultClass Imhonet\Connection\Resource\Sphinx
 * @package Imhonet\Connection\Resource
 */
class SphinxTest extends \PHPUnit_Framework_TestCase
{
    const HOST = '127.0.0.1';
    const PORT = 3312;

    /**
     * @var Sphinx
     */
    private $resource;

    protected function setUp()
    {
        $this->resource = new Sphinx();
        $this->resource
            ->setHost(self::HOST)
            ->setPort(self::PORT)
        ;
    }

    /**
     * @covers ::__construct
     */
    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\\Connection\\Resource\\IResource', $this->resource);
    }

    /**
     * @covers ::getHandle
     */
    public function testHandler()
    {
        $this->assertInstanceOf('\\SphinxClient', $this->resource->getHandle());
    }

    /**
     * @covers ::setHost
     * @covers ::setPort
     */
    public function testSetServer()
    {
        /** @var \SphinxClient $handle */
        $handle = $this->resource->getHandle();

        $this->assertEquals(self::HOST, $handle->_host);
        $this->assertEquals(self::PORT, $handle->_port);
    }

    protected function tearDown()
    {
        $this->resource = null;
    }
}
