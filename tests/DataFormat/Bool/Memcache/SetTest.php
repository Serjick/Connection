<?php

namespace Imhonet\Connection\DataFormat\Bool\Memcache;

use Imhonet\Connection\DataFormat\IBool;

class SetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IBool
     */
    private $formater;

    protected function setUp()
    {
        $this->formater = new Set();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IBool', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData(true);
        $this->assertEquals(array(), $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData(true);
        $this->assertTrue($this->formater->formatValue());
    }

    public function testFailure()
    {
        $this->formater->setData(false);
        $this->assertFalse($this->formater->formatValue());
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
