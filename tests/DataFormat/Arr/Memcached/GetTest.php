<?php

namespace Imhonet\Connection\DataFormat\Arr\Memcached;

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
        $this->formater->setData($this->data);
        $this->assertEquals($this->data, $this->formater->formatData());
    }

    public function testValue()
    {
        $this->formater->setData($this->data);
        $this->assertNull($this->formater->formatValue());
    }

    public function testFailure()
    {
        $this->formater->setData(false);
        $this->assertNotEquals(array(), $this->formater->formatData());
    }

    protected function tearDown()
    {
        $this->formater = null;
    }
}
