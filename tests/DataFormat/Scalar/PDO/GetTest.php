<?php

namespace Imhonet\Connection\Test\DataFormat\Scalar\PDO;

use Imhonet\Connection\DataFormat\IScalar;
use Imhonet\Connection\DataFormat\Scalar\PDO\Get;

class GetTest extends \PHPUnit_Framework_TestCase
{
    const VALUE = 42;

    /**
     * @var IScalar
     */
    private $formater;

    protected function setUp()
    {
        $this->formater = new Get();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IScalar', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData($this->getStmt($this->never()));
        $this->assertEquals(array(), $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData($this->getStmt($this->at(0)));
        $this->assertEquals(self::VALUE, $this->formater->formatValue());
    }

    public function testReuse()
    {
        $this->formater->setData($this->getStmt($this->at(0)));
        $this->formater->formatValue();
        $this->assertEquals(self::VALUE, $this->formater->formatValue());
    }

    public function testFailure()
    {
        $this->formater->setData(false);
        $this->assertNull($this->formater->formatValue());
    }

    public function getStmt(\PHPUnit_Framework_MockObject_Matcher_Invocation $fetch)
    {
        $stmt = $this->getMock('\\PDOStatement', array('fetchColumn'));
        $stmt
            ->expects($fetch)
            ->method('fetchColumn')
            ->will($this->returnValue(self::VALUE))
        ;

        return $stmt;
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
