<?php

namespace Imhonet\Connection\Test\DataFormat\Bool\PDO;

use Imhonet\Connection\DataFormat\Bool\PDO\Set;
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
        $this->formater->setData($this->getStmt());
        $this->assertEquals(array(), $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData($this->getStmt());
        $this->assertTrue($this->formater->formatValue());
    }

    public function getStmt()
    {
        $stmt = $this->getMock('\\PDOStatement', array('rowCount'));
        $stmt
            ->expects($this->any())
            ->method('rowCount')
            ->will($this->returnValue(10))
        ;

        return $stmt;
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
