<?php

namespace Imhonet\Connection\Test\DataFormat\Arr\Sphinx;

use Imhonet\Connection\DataFormat\Arr\Sphinx\Rekey;

/**
 * Class RekeyTest
 * @covers \Imhonet\Connection\DataFormat\Arr\Sphinx\Rekey::
 * @coversDefaultClass \Imhonet\Connection\DataFormat\Arr\Sphinx\Rekey
 * @package Imhonet\Connection\Test\DataFormat
 */
class RekeyTest extends \PHPUnit_Framework_TestCase
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
     * @var Rekey
     */
    private $formater;

    protected function setUp()
    {
        $this->formater = new Rekey();
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
    public function testNewKey()
    {
        $response = json_decode(self::RESPONSE, true);
        $this->formater
            ->setData($response)
            ->setNewKey('sort_field')
        ;
        $this->assertEquals(
            array(
                3 => $response[0]['matches'][1148685]['attrs'],
                50 => $response[0]['matches'][9764681]['attrs'],
                51 => $response[0]['matches'][9782242]['attrs'],
            ),
            $this->formater->formatData()
        );
    }

    /**
     * @covers ::formatData
     */
    public function testValueKey()
    {
        $response = json_decode(self::RESPONSE, true);
        $this->formater
            ->setData($response)
            ->setValueKey('id')
        ;
        $this->assertEquals([1148685, 9764681, 9782242], $this->formater->formatData());
    }

    /**
     * @covers ::formatData
     */
    public function testNewAndValueKeys()
    {
        $response = json_decode(self::RESPONSE, true);
        $this->formater
            ->setData($response)
            ->setNewKey('sort_field')
            ->setValueKey('id')
        ;
        $this->assertEquals([3 => 1148685, 50 => 9764681, 51 => 9782242], $this->formater->formatData());
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
