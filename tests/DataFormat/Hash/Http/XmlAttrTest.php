<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\IHash;

require_once 'functions.php';

class XmlAttrTest extends \PHPUnit_Framework_TestCase
{
    private static $data = '<response status="200" message="OK" />';

    /**
     * @var IHash
     */
    private $formater;

    public static function setUpBeforeClass()
    {
        putenv('TEST_CLASS=' . __CLASS__);
    }

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
        $this->formater->setData(curl_init());
        $this->assertEquals(array('status' => 200, 'message' => 'OK'), $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData(curl_init());
        $this->assertNull($this->formater->formatValue());
    }

    /**
     * @todo
     */
    public function testFailure()
    {
    }

    public static function getData()
    {
        return self::$data;
    }

    protected function tearDown()
    {
        $this->formater = null;
    }

    public static function tearDownAfterClass()
    {
        putenv('TEST_CLASS');
    }
}
