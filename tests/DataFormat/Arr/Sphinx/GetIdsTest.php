<?php

namespace Imhonet\Connection\DataFormat\Arr\Sphinx;

use Imhonet\Connection\DataFormat\IArr;

/**
 * Class GetIdsTest
 * @covers \Imhonet\Connection\DataFormat\Arr\Sphinx\GetIds::
 * @coversDefaultClass \Imhonet\Connection\DataFormat\Arr\Sphinx\GetIds
 * @package Imhonet\Connection\DataFormat
 */
class GetIdsTest extends \PHPUnit_Framework_TestCase
{
    const RESPONSE = '[{
        "error":"",
        "warning":"",
        "status":0,
        "fields":["title", "description"],
        "attrs":{"attr_id":1, "id":1, "sort_field":1},
        "matches":{
            "1148685":{
                "weight":"1",
                "attrs":{"attr_id":1148685, "id":1148685, "sort_field":3}
            },
            "9764681":{
                "weight":"1",
                "attrs":{"attr_id":9764681, "id":9764681, "sort_field":50}
            },
            "9782242":{
                "weight":"1",
                "attrs":{"attr_id":9782242, "id":9782242, "sort_field":51}
            }
        },
        "total":"1000",
        "total_found":"79082",
        "time":"0.015"
    }]';

    /**
     * @var IArr
     */
    private $formater;

    protected function setUp()
    {
        $this->formater = new GetIds();
    }

    /**
     * @covers ::__construct
     */
    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\\Connection\\DataFormat\\IArr', $this->formater);
    }

    /**
     * @covers ::formatData
     */
    public function testData()
    {
        $response = json_decode(self::RESPONSE, true);
        $this->formater->setData($response);
        $this->assertEquals(array(1148685, 9764681, 9782242), $this->formater->formatData());
    }

    /**
     * @covers ::formatValue
     */
    public function testValue()
    {
        $this->formater->setData(json_decode(self::RESPONSE, true));
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
