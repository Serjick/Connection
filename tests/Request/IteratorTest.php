<?php

namespace Imhonet\Connection;

class IteratorTest extends \PHPUnit_Framework_TestCase
{
    private $data = array(
        ['index' => 2, 'data' => ['id' => 10]],
        ['index' => 0, 'data' => ['id' => 1]],
        ['index' => 1, 'data' => []],
    );

    public function testTraversable()
    {
        $request = new Request($this->getQueryMock(), $this->getFormatMock());

        $this->assertInstanceOf('\\Traversable', $request);
    }

    public function testTraverse()
    {
        $request = new Request($this->getQueryMockPrepared(), $this->getFormatMockPrepared());

        $i = 0;
        foreach ($request as $index => $data) {
            $this->assertEquals($this->data[$i]['index'], $index);
            $this->assertEquals($this->data[$i]['data'], $data);
            $i++;
        }

        $this->assertEquals(count($this->data), $i);
    }

    private function getQueryMockPrepared()
    {
        $query = $this->getQueryMock();

        $query
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(curl_multi_init()))
        ;

        return $query;
    }

    private function getFormatMockPrepared()
    {
        $format = $this->getFormatMock();

        $format
            ->expects($this->any())
            ->method('setData')
            ->will($this->returnSelf())
        ;

        $call = 0;
        foreach ($this->data as $i => $data) {
            $format
                ->expects($this->at(++$call))
                ->method('formatData')
                ->will($this->returnValue($data['data']))
            ;
            $format
                ->expects($this->at(++$call))
                ->method('getIndex')
                ->will($this->returnValue($data['index']))
            ;
            $format
                ->expects($this->at(++$call))
                ->method('getIndex')
                ->will($this->returnValue($data['index']))
            ;
            $format
                ->expects($this->at(++$call))
                ->method('moveNext')
                ->will($this->returnValue($i < count($this->data)-1))
            ;
        }

        return $format;
    }

    /**
     * @return \Imhonet\Connection\DataFormat\IMulti|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getFormatMock()
    {
        $format = $this->getMock('\\Imhonet\\Connection\\DataFormat\\IMulti', array(
            'setData',
            'getIndex',
            'moveNext',
            'formatData',
            'formatValue',
        ));

        return $format;
    }

    /**
     * @return \Imhonet\Connection\Query\IQuery|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQueryMock()
    {
        $query = $this->getMock('\\Imhonet\\Connection\\Query\\IQuery', array(
            'setResource',
            'execute',
            'getErrorCode',
            'getCountTotal',
            'getCount',
            'getLastId',
            'getDebugInfo',
            'seek',
            'current',
            'next',
            'key',
            'valid',
            'rewind',
            'getCacheKey',
            'disableQuery',
            'getExpire',
            'getTags',
        ));

        return $query;
    }

    protected function tearDown()
    {
        $this->request = null;
    }
}
