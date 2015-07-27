<?php

namespace Imhonet\Connection\Test\DataFormat\Arr\PDO;

use Imhonet\Connection\DataFormat\Arr\PDO\Get;
use Imhonet\Connection\DataFormat\Arr\PDO\Rekey;
use Imhonet\Connection\DataFormat\IArr;

class RekeyTest extends \PHPUnit_Framework_TestCase
{
    private $data = array(
        ['one' => 1, 'two' => 2, 'three' => 4],
        ['one' => 2, 'two' => 4, 'three' => 6],
        ['one' => 3, 'two' => 5, 'three' => 7],
    );

    /**
     * @var IArr
     */
    private $formater;

    /**
     * @todo use Rekey formatter
     */
    protected function setUp()
    {
        $this->formater = new Get();
    }

    public function testCreate()
    {
        $this->markTestSkipped('@todo use Rekey formatter');

        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IArr', $this->formater);
    }

    public function testData()
    {
        $this->markTestSkipped('@todo use Rekey formatter');

        $this->formater->setData($this->getStmt());
        $this->assertEquals($this->data, $this->formater->formatData());
    }

    public function testValue()
    {
        $this->markTestSkipped('@todo use Rekey formatter');

        $this->formater->setData($this->getMock('\\PDOStatement'));
        $this->assertNull($this->formater->formatValue());
    }

    public function testReuse()
    {
        $this->markTestSkipped('@todo use Rekey formatter');

        $this->formater->setData($this->getStmt());
        $this->formater->formatData();
        $this->assertEquals($this->data, $this->formater->formatData());
    }

    public function testDecorate()
    {
        $this->markTestSkipped('@todo mock \\PDOStatement foreach traverse');

        $formatter = (new Rekey())
            ->setData($this->getStmt())
            ->setNewKey('one')
            ->setFormatter((new Rekey())->setNewKey('two'))
        ;
        $this->assertEquals(
            [1 => ['three' => 4], 2 => ['three' => 6], 3 => ['three' => 7]],
            $formatter->formatData()
        );
    }

    public function getStmt()
    {
        $stmt = $this->getMock('\\PDOStatement', array('fetchAll', 'closeCursor'));
        $stmt
            ->expects($this->at(0))
            ->method('fetchAll')
            ->will($this->returnValue($this->data));
        $stmt
            ->expects($this->at(1))
            ->method('closeCursor');

        return $stmt;
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
