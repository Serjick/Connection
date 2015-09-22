<?php

namespace Imhonet\Connection\Resource {

    class HandlerSocketTest extends \PHPUnit_Framework_TestCase
    {
        const HOST = '127.0.0.1';
        const DB = 'user';

        public static $counter = 0;

        /**
         * @var HandlerSocket
         */
        private $resource;

        protected function setUp()
        {
            self::$counter = 0;

            $this->resource = (new HandlerSocket())
                ->setHost(self::HOST)
                ->setDatabase(self::DB);
        }

        /**
         * @covers ::__construct
         */
        public function testCreate()
        {
            $this->assertInstanceOf('\\Imhonet\\Connection\\Resource\\IResource', $this->resource);
        }

        /**
         * @covers ::getHandle
         */
        public function testHandler()
        {
            $this->assertInstanceOf('\\HSPHP\\ReadSocket', $this->resource->getHandle());
            $this->assertEquals(1, self::$counter);
        }

        /**
         * @covers ::getHandle
         */
        public function testReuse()
        {
            $this->resource->getHandle();
            $this->resource->getHandle();

            $this->assertEquals(1, self::$counter);
        }

        protected function tearDown()
        {
            $this->resource = null;
        }
    }
}

namespace HSPHP {

    use Imhonet\Connection\Resource\HandlerSocketTest;

    function stream_socket_client($remote_socket, &$errno = null, &$errstr = null, $timeout = null, $flags = null, $context = null)
    {
        unset($errno, $errstr, $timeout, $flags, $context);

        if (strpos($remote_socket, HandlerSocketTest::HOST) !== false) {
            ++HandlerSocketTest::$counter;
        }

        return true;
    }
}
