<?php

namespace Imhonet\Connection\Test\DataFormat\Iter\PDO;

use Imhonet\Connection\DataFormat\IIter;
use Imhonet\Connection\DataFormat\Iter\PDO\Get;

class GetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IIter
     */
    private $formater;

    protected function setUp()
    {
        $this->formater = new Get();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IIter', $this->formater);
    }

    public function testData()
    {
        $this->formater->setData($this->getStmt());
        $this->assertInstanceOf('\\Traversable', $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData($this->getStmt());
        $this->assertNull($this->formater->formatValue());
    }

    public function testFailure()
    {
        $this->formater->setData(false);
        $this->assertInstanceOf('\\Traversable', $this->formater->formatData());
    }

    public function getStmt()
    {
        return $this->getMock('\\PDOStatement');
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
