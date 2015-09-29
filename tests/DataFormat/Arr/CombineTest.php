<?php

namespace Imhonet\Connection\DataFormat\Arr;

class CombineTest extends \PHPUnit_Framework_TestCase
{
    const USER1_ID = 5;
    const USER1_LOGIN = 'user1_login';
    const USER2_ID = 10;
    const USER2_LOGIN = 'user2_login';

    private $data = [
        array(self::USER1_ID, self::USER1_LOGIN),
        array(self::USER2_ID, self::USER2_LOGIN),
    ];

    /**
     * @var Combine
     */
    private $formatter;

    protected function setUp()
    {
        $this->formatter = new Combine();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Imhonet\Connection\DataFormat\IArr', $this->formatter);
    }

    public function testData()
    {
        $key1 = 'id';
        $key2 = 'login';

        $this->formatter
            ->setData($this->data)
            ->setKeys([$key1, $key2]);
        $expected = [
            [$key1 => self::USER1_ID, $key2 => self::USER1_LOGIN],
            [$key1 => self::USER2_ID, $key2 => self::USER2_LOGIN],
        ];
        $this->assertEquals($expected, $this->formatter->formatData());
    }

    public function testValue()
    {
        $this->formatter->setData($this->data);
        $this->assertNull($this->formatter->formatValue());
    }

    public function testFailureData()
    {
        $this->formatter->setData(null)->setKeys(['some_key']);
        $this->assertEquals(array(), $this->formatter->formatData());
    }

    public function testFailureKeys()
    {
        assert_options(ASSERT_WARNING, 0);

        $this->formatter->setData($this->data);
        $this->assertEquals(array(), $this->formatter->formatData());
    }

    protected function tearDown()
    {
        $this->formatter = null;
    }
}
