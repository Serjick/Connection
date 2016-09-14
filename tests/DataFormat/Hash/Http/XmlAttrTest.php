<?php

use Imhonet\Connection\DataFormat\IHash;

require_once 'functions.php';

class XmlAttrTest extends \PHPUnit_Framework_TestCase
{
    const MODE_REGULAR = 1;
    const MODE_FAILURE = 2;

    private static $data = array(
        self::MODE_REGULAR => '<response status="200" message="OK" />',
        self::MODE_FAILURE => '',
    );
    private static $mode;

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
        self::$mode = self::MODE_REGULAR;
        $this->formater = new \Imhonet\Connection\DataFormat\Hash\Http\XmlAttr();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('\\Imhonet\\Connection\\DataFormat\\IHash', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData($this->getResponse());
        $this->assertEquals(array('status' => 200, 'message' => 'OK'), $this->formater->formatData());
    }

    public function testFailure()
    {
        self::$mode = self::MODE_FAILURE;
        $this->formater->setData($this->getResponse());
        $this->assertEquals(array(), $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData($this->getResponse());
        $this->assertNull($this->formater->formatValue());
    }

    /**
     * @return \Imhonet\Connection\Response\Http
     */
    private function getResponse()
    {
        return (new \Imhonet\Connection\Response\Http)
            ->setMultiHandle(curl_init())
            ->addHandle(curl_init())
        ;
    }

    public static function getData()
    {
        return self::$data[self::$mode];
    }

    protected function tearDown()
    {
        $this->formater = null;
        self::$mode = null;
    }

    public static function tearDownAfterClass()
    {
        putenv('TEST_CLASS');
    }
}
