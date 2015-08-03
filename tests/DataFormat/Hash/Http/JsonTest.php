<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\IHash;

require_once 'functions.php';

class JsonTest extends \PHPUnit_Framework_TestCase
{
    const MODE_REGULAR = 1;
    const MODE_MALFORMED = 2;

    private static $data = array(
        self::MODE_REGULAR => array(
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ),
        self::MODE_MALFORMED => [array(
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        )],
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
        $this->formater = new Json();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IHash', $this->formater);
    }

    public function testData()
    {
        $this->assertEquals(self::$data[self::$mode], $this->formater->formatData());
    }

    public function testDataMalformed()
    {
        self::$mode = self::MODE_MALFORMED;
        $this->assertEquals(array(), $this->formater->formatData());
    }

    public function testValue()
    {
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

