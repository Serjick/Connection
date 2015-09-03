<?php

namespace Imhonet\Connection\Query\Elastic;

use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use GuzzleHttp\Ring\Future\CompletedFutureArray;
use Imhonet\Connection\Resource\IResource;

class SearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Search
     */
    private $query;

    protected function setUp()
    {
        $this->query = new Search();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('\\Imhonet\\Connection\\Query\\IQuery', $this->query);
        $this->assertInstanceOf('\\Imhonet\\Connection\\Query\\Query', $this->query);
    }

    public function testFormat()
    {
        $this->query
            ->withIndex('test')
            ->setResource($this->getResource());
        $result = $this->query->execute();

        $this->assertInternalType('array', $result);

        foreach ($result as $responses) {
            $this->assertInstanceOf('\\GuzzleHttp\\Ring\\Future\\FutureArrayInterface', $responses);
        }
    }

    public function testData()
    {
        $this->query
            ->withIndex('test')
            ->setResource($this->getResource());

        foreach ($this->query->execute() as $responses) {
            $result = array();

            foreach ($responses['hits']['hits'] as $row) {
                $result[] = $row['_id'];
            }

            $this->assertEquals([123, 321], $result);
        }
    }

    public function testCounts()
    {
        $this->query
            ->withIndex('test')
            ->setResource($this->getResource());

        $this->assertEquals(2, $this->query->getCount());
        $this->assertEquals(3, $this->query->getCountTotal());
    }

    public function testFailure()
    {
        $this->query
            ->withIndex('test')
            ->setResource($this->getResourceFailed());

        $this->assertEquals(1, $this->query->getErrorCode());
    }

    /**
     * @return IResource
     */
    private function getResource()
    {
        $handle = $this->getMockBuilder('\\Elasticsearch\\Client')
            ->setMethods(['search'])
            ->disableOriginalConstructor()
            ->getMock();
        $handle->expects($this->once())
            ->method('search')
            ->will($this->returnValue($this->getResponse()));

        $resource = $this->getMock('\\Imhonet\\Connection\\Resource\\Elastic', array('getHandle'));
        $resource->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($handle));

        return $resource;
    }

    /**
     * @return IResource
     */
    private function getResourceFailed()
    {
        $handle = $this->getMockBuilder('\\Elasticsearch\\Client')
            ->setMethods(['search'])
            ->disableOriginalConstructor()
            ->getMock();
        $handle->expects($this->once())
            ->method('search')
            ->will($this->throwException(new BadRequest400Exception));

        $resource = $this->getMock('\\Imhonet\\Connection\\Resource\\Elastic', array('getHandle'));
        $resource->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($handle));

        return $resource;
    }

    private function getResponse()
    {
        $result = array(
            'total' => 3,
            'hits' => array(
                array(
                    '_id' => 123,
                ),
                array(
                    '_id' => 321,
                ),
            )
        );

        return new CompletedFutureArray(array('hits' => $result));
    }

    protected function tearDown()
    {
        $this->query = null;
    }
}
