<?php

namespace Imhonet\Connection\Test\Resource;

use Imhonet\Connection\Resource\Elastic;

class ElasticTest extends \PHPUnit_Framework_TestCase
{
    const HOST = '127.0.0.1';
    const PORT = 9200;

    /**
     * @var Elastic
     */
    private $resource;

    protected function setUp()
    {
        $this->resource = (new Elastic())
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
        $this->assertInstanceOf('\\Elasticsearch\\Client', $this->resource->getHandle());
    }

    protected function tearDown()
    {
        $this->resource = null;
    }
}
