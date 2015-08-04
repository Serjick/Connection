<?php

namespace Imhonet\Connection\DataFormat\Hash\PDO;

use Imhonet\Connection\DataFormat\IHash;

class GetTest extends \PHPUnit_Framework_TestCase
{
    private $result = array('id' => 42);
    /**
     * @var IHash
     */
    private $formater;

    protected function setUp()
    {
        $this->formater = new Get();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IHash', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData($this->getStmt($this->at(0)));
        $this->assertEquals($this->result, $this->formater->formatData());
    }

    public function testReuse()
    {
        $this->formater->setData($this->getStmt($this->at(0)));
        $this->formater->formatData();
        $this->assertEquals($this->result, $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData($this->getStmt($this->never()));
        $this->assertNull($this->formater->formatValue());
    }

    public function testFailure()
    {
        $this->formater->setData(false);
        $this->assertInternalType('array', $this->formater->formatData());
    }

    public function getStmt(\PHPUnit_Framework_MockObject_Matcher_Invocation $fetch)
    {
        $mock = $this->getMock('\\PDOStatement', array('fetch'));

        $mock
            ->expects($fetch)
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->will($this->returnValue($this->result))
        ;

        return $mock;
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
