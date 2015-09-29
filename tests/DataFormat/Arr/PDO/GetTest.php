<?php

namespace Imhonet\Connection\DataFormat\Arr\PDO;

use Imhonet\Connection\DataFormat\IArr;

class GetTest extends \PHPUnit_Framework_TestCase
{
    private $data = array(
        'false' => false,
        'true' => true,
        'null' => null,
        'int' => 42,
        'string' => 'forty two',
        'arr' => array(42),
    );

    /**
     * @var IArr
     */
    private $formater;

    protected function setUp()
    {
        $this->formater = new Get();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IArr', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData($this->getStmt());
        $this->assertEquals($this->data, $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData($this->getMock('\\PDOStatement'));
        $this->assertNull($this->formater->formatValue());
    }

    public function testReuse()
    {
        $this->formater->setData($this->getStmt());
        $this->formater->formatData();
        $this->assertEquals($this->data, $this->formater->formatData());
    }

    public function getStmt()
    {
        $stmt = $this->getMock('\\PDOStatement', array('fetchAll', 'closeCursor'));
        $stmt
            ->expects($this->at(0))
            ->method('fetchAll')
            ->will($this->returnValue($this->data))
        ;
        $stmt
            ->expects($this->at(1))
            ->method('closeCursor')
        ;

        return $stmt;
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
