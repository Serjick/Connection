<?php

namespace Imhonet\Connection\DataFormat\Arr\Elastic;

use GuzzleHttp\Ring\Future\CompletedFutureArray;

class SearchTest extends \PHPUnit_Framework_TestCase
{
    private $ids = [12345, 1545, 1123];

    /**
     * @var Search
     */
    private $formater;

    protected function setUp()
    {
        $this->formater = new Search();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IArr', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData($this->getData());
        $this->assertEquals($this->ids, $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData($this->getData());
        $this->assertNull($this->formater->formatValue());
    }

    public function testFailure()
    {
        assert_options(ASSERT_WARNING, 0);

        $this->formater->setData(null);
        $this->assertEquals(array(), $this->formater->formatData());
    }

    public function getData()
    {
        $result = array(
            'total' => sizeof($this->ids),
            'hits' => array(),
        );

        foreach ($this->ids as $id) {
            $result['hits'][] = array('_id' => $id);
        }

        return [new CompletedFutureArray(array('hits' => $result))];
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
