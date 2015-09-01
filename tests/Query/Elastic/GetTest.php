<?php

namespace Imhonet\Connection\Query\Elastic;

use GuzzleHttp\Ring\Future\CompletedFutureArray;
use Imhonet\Connection\Resource\IResource;

class GetTest extends \PHPUnit_Framework_TestCase
{
    private $data = [
        8471 => array(
            'blog_id' => 8471,
            'title' => 'title1',
            'text' => 'text1',
            'user_id' => 777,
        ),
        -1 => null,
        2155 => array(
            'blog_id' => 2155,
            'title' => 'title2',
            'text' => 'text2',
            'user_id' => 128,
        ),
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
        $this->assertInstanceOf('\\Imhonet\\Connection\\Query\\Query', $this->query);
    }

    public function testFormat()
    {
        $this->query
            ->addIds(array_keys($this->data))
            ->setResource($this->getResource());
        $result = $this->query->execute();

        $this->assertInternalType('array', $result);

        foreach ($result as $responses) {
            $this->assertInstanceOf('\\GuzzleHttp\\Ring\\Future\\FutureArrayInterface', $responses);
        }
    }

    public function testFound()
    {
        $this->query
            ->addIds(array_keys($this->data))
            ->setResource($this->getResource());

        foreach ($this->query->execute() as $responses) {
            foreach ($responses as $response) {
                foreach ($response as $row) {
                    $id = $row['_id'];
                    $this->assertEquals(isset($this->data[$id]), $row['found']);
                }
            }
        }
    }

    public function testData()
    {
        $this->query
            ->addIds(array_keys($this->data))
            ->setResource($this->getResource());

        foreach ($this->query->execute() as $responses) {
            foreach ($responses as $response) {
                foreach ($response as $row) {
                    $id = $row['_id'];
                    $data = isset($row['_source']) ? $row['_source'] : null;
                    $this->assertEquals($this->data[$id], $data);
                }
            }
        }
    }

    public function testCount()
    {
        // @todo
    }

    public function testFailure()
    {
        // @todo
    }

    /**
     * @return IResource
     */
    private function getResource()
    {
        $handle = $this->getMockBuilder('\\Elasticsearch\\Client')
            ->setMethods(['mget'])
            ->disableOriginalConstructor()
            ->getMock();
        $handle->expects($this->once())
            ->method('mget')
            ->will($this->returnCallback(function (array $params) {
                return $this->callMGet($params);
            }));

        $resource = $this->getMock('\\Imhonet\\Connection\\Resource\\Elastic', array('getHandle'));
        $resource->expects($this->any())
            ->method('getHandle')
            ->will($this->returnValue($handle));

        return $resource;
    }

    private function callMGet(array $params)
    {
        $result = array();

        foreach ($params['body']['ids'] as $id) {
            $found = isset($this->data[$id]);

            $response = array(
                '_id' => $id,
                'found' => $found,
            );

            if ($found) {
                $response['_source'] = is_array($params['_source'])
                    ? array_intersect_key(array_flip($params['_source']), $this->data[$id])
                    : $this->data[$id];
            }

            $result[] = $response;
        }

        return new CompletedFutureArray(array('docs' => $result));
    }

    protected function tearDown()
    {
        $this->query = null;
    }
}
