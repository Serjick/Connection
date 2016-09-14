<?php

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
     * @var \Imhonet\Connection\DataFormat\Hash\Http\Json
     */
    private $formater;

    public static function setUpBeforeClass()
    {
        putenv('TEST_CLASS=' . __CLASS__);
    }

    protected function setUp()
    {
        self::$mode = self::MODE_REGULAR;
        $this->formater = new \Imhonet\Connection\DataFormat\Hash\Http\Json();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('\\Imhonet\\Connection\\DataFormat\\IHash', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData($this->getResponse());
        $this->assertEquals(self::$data[self::$mode], $this->formater->formatData());
    }

    public function testDataFailure()
    {
        self::$mode = self::MODE_FAILURE;
        $this->formater->setData($this->getResponse());
        $this->assertEquals(array(), $this->formater->formatData());
    }

    public function testDataMalformed()
    {
        self::$mode = self::MODE_MALFORMED;
        $this->formater->setData($this->getResponse());
        $this->assertEquals(array(), $this->formater->formatData());
    }

    public function testIndex()
    {
        $id = 123;
        $resource = curl_init();
        curl_setopt($resource, \CURLOPT_PRIVATE, json_encode(['id' => $id]));
        $this->formater->setData($this->getResponse($resource));
        $this->assertEquals($id, $this->formater->getIndex());
    }

    public function testValue()
    {
        $this->formater->setData($this->getResponse());
        $this->assertNull($this->formater->formatValue());
    }

    /**
     * @param resource|null $resource
     * @return \Imhonet\Connection\Response\Http
     */
    private function getResponse($resource = null)
    {
        $resource = $resource ? : curl_init();

        return (new \Imhonet\Connection\Response\Http)
            ->setMultiHandle($resource)
            ->addHandle($resource)
        ;
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
