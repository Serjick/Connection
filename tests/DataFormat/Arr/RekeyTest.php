<?php

namespace Imhonet\Connection\DataFormat\Arr;

class RekeyTest extends \PHPUnit_Framework_TestCase
{
    const USER1_ID = 5;
    const USER1_LOGIN = 'user1_login';
    const USER1_OLDLOGIN = 'user1_oldlogin';
    const USER2_ID = 10;
    const USER2_LOGIN = 'user2_login';

    private $data = [
        array('id' => self::USER1_ID, 'login' => self::USER1_OLDLOGIN),
        array('id' => self::USER1_ID, 'login' => self::USER1_LOGIN),
        array('id' => self::USER2_ID, 'login' => self::USER2_LOGIN),
    ];

    /**
     * @var Rekey
     */
    private $formatter;

    protected function setUp()
    {
        $this->formatter = new Rekey();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('\\Imhonet\\Connection\\DataFormat\\IArr', $this->formatter);
    }

    public function testIndexKey()
    {
        $this->formatter
            ->setData($this->data)
            ->setIndexKey('id');
        $expected = array_combine([self::USER1_ID, self::USER1_ID, self::USER2_ID], $this->data);
        $this->assertEquals($expected, $this->formatter->formatData());
    }

    public function testValueKey()
    {
        $this->formatter
            ->setData($this->data)
            ->setIndexKey('id')
            ->setValueKey('login');
        $expected = [self::USER1_ID => self::USER1_LOGIN, self::USER2_ID => self::USER2_LOGIN];
        $this->assertEquals($expected, $this->formatter->formatData());
    }

    public function testGrouping()
    {
        $this->formatter
            ->setData($this->data)
            ->setIndexKey('id')
            ->setValueKey('login')
            ->setGrouping();
        $expected = array(
            self::USER1_ID => [self::USER1_OLDLOGIN, self::USER1_LOGIN],
            self::USER2_ID => [self::USER2_LOGIN]
        );
        $this->assertEquals($expected, $this->formatter->formatData());
    }

    public function testDecorator()
    {
        $this->formatter
            ->setFormatter($this->getWrappedFormatter())
            ->setData($this->data)
            ->setIndexKey('id');
        $expected = array_combine([self::USER1_ID, self::USER1_ID, self::USER2_ID], $this->data);
        $this->assertEquals($expected, $this->formatter->formatData());
    }

    public function testValue()
    {
        $this->formatter->setData($this->data);
        $this->assertNull($this->formatter->formatValue());
    }

    public function testFailureData()
    {
        $this->formatter->setData(null)->setIndexKey(['some_key']);
        $this->assertEquals(array(), $this->formatter->formatData());
    }

    public function testFailureKey()
    {
        assert_options(ASSERT_WARNING, 0);

        $this->formatter->setData($this->data);
        $this->assertEquals(array(), $this->formatter->formatData());
    }

    private function getWrappedFormatter()
    {
        $mock = $this->getMock(
            '\\Imhonet\\Connection\\DataFormat\\IDataFormat',
            ['setData', 'formatData', 'formatValue']
        );
        $mock->expects($this->at(0))
            ->method('setData')
            ->with($this->data)
            ->willReturnSelf();
        $mock->expects($this->at(1))
            ->method('formatData')
            ->will($this->returnValue($this->data));

        return $mock;
    }

    protected function tearDown()
    {
        $this->formatter = null;
    }
}
