<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;

require_once 'functions.php';

class JsonTest extends \PHPUnit_Framework_TestCase
{
    const MODE_REGULAR = 1;
    const MODE_FAILURE = 2;
    const MODE_MALFORMED = 3;

    private static $data = array(
        self::MODE_REGULAR => array(
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ),
        self::MODE_FAILURE => '',
        self::MODE_MALFORMED => [array(
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        )],
    );
    private static $mode;

    /**
     * @var Json
     */
    private $formater;

    public static function setUpBeforeClass()
    {
        putenv('TEST_CLASS=' . __CLASS__);
    }

    protected function setUp()
    {
        self::$mode = self::MODE_REGULAR;
        $this->formater = new Json();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IHash', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData(curl_init());
        $this->assertEquals(self::$data[self::$mode], $this->formater->formatData());
    }

    public function testDataFailure()
    {
        self::$mode = self::MODE_FAILURE;
        $this->formater->setData(curl_init());
        $this->assertEquals(array(), $this->formater->formatData());
    }

    public function testDataMalformed()
    {
        self::$mode = self::MODE_MALFORMED;
        $this->formater->setData(curl_init());
        $this->assertEquals(array(), $this->formater->formatData());
    }

    public function testIndex()
    {
        $id = 123;
        $resource = curl_init();
        curl_setopt($resource, \CURLOPT_PRIVATE, json_encode(['id' => $id]));
        $this->formater->setData($resource);
        $this->assertEquals($id, $this->formater->getIndex());
    }

    public function testValue()
    {
        $this->formater->setData(curl_init());
        $this->assertNull($this->formater->formatValue());
    }

    public static function getData()
    {
        return json_encode(self::$data[self::$mode]);
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
