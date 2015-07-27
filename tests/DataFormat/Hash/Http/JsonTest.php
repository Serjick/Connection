<?php

namespace Imhonet\Connection\Test\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\Hash\Http\Json;
use Imhonet\Connection\DataFormat\IHash;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    private $data = array(
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    );

    /**
     * @var IHash
     */
    private $formater;

    protected function setUp()
    {
        $this->formater = new Json();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IHash', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData($this->getData());
        $this->assertEquals($this->data, $this->formater->formatData());
    }

    public function testDataMalformed()
    {
        $this->formater->setData('[' . $this->getData() . ']');
        $this->assertEquals(array(), $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData($this->getData());
        $this->assertNull($this->formater->formatValue());
    }

    public function testFailure()
    {
        $this->formater->setData(false);
        $this->assertEquals(array(), $this->formater->formatData());
    }

    protected function getData()
    {
        return json_encode($this->data);
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
