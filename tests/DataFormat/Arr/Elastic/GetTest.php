<?php

namespace Imhonet\Connection\DataFormat\Arr\Elastic;


use GuzzleHttp\Ring\Future\CompletedFutureArray;

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
    private $formater;

    protected function setUp()
    {
        $this->formater = new Get();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IArr', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData($this->getData());
        $this->assertEquals(array_values(array_filter($this->data)), $this->formater->formatData());
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
        $result = array();

        foreach ($this->data as $id => $row) {
            $found = (bool) $row;

            $response = array(
                '_id' => $id,
                'found' => $found,
            );

            if ($found) {
                $response['_source'] = $row;
            }

            $result[] = $response;
        }

        return [new CompletedFutureArray(array('docs' => $result))];
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
