<?php

namespace Imhonet\Connection\Test\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\Hash\Http\XmlAttr;
use Imhonet\Connection\DataFormat\IHash;

class XmlAttrTest extends \PHPUnit_Framework_TestCase
{
    private $data = '<response status="200" message="OK" />';

    /**
     * @var IHash
     */
    private $formater;

    protected function setUp()
    {
        $this->formater = new XmlAttr();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IHash', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData($this->data);
        $this->assertEquals(array('status' => 200, 'message' => 'OK'), $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData($this->data);
        $this->assertNull($this->formater->formatValue());
    }

    public function testFailure()
    {
        $this->formater->setData(false);
        $this->assertEquals(array(), $this->formater->formatData());
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
