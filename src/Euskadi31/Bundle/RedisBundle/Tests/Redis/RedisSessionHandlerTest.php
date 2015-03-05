<?php

namespace Euskadi31\Bundle\RedisBundle\Tests\Redis;

use Euskadi31\Bundle\RedisBundle\Redis\RedisSessionHandler;
use RedisException;

class RedisSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $redisMock = $this->getMock('Redis');

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertEquals($redisMock, $s->getRedis());
    }

    public function testOpen()
    {
        $redisMock = $this->getMock('Redis');

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertTrue($s->open('/', 'toto'));
    }

    public function testClose()
    {
        $redisMock = $this->getMock('Redis');

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertTrue($s->close());
    }

    public function testGc()
    {
        $redisMock = $this->getMock('Redis');

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertTrue($s->gc(10));
    }

    public function testWrite()
    {
        $redisMock = $this->getMock('Redis');
        $redisMock->method('set')
            ->with($this->equalTo('foo'), $this->equalTo('bar'), $this->equalTo(11));

        $s = new RedisSessionHandler($redisMock, 11);

        $this->assertTrue($s->write('foo', 'bar'));
    }

    public function testWriteError()
    {
        $redisMock = $this->getMock('Redis');
        $redisMock->method('set')
            ->with($this->equalTo('foo1'), $this->equalTo('bar1'), $this->equalTo(10))
            ->will($this->throwException(new RedisException));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertFalse($s->write('foo1', 'bar1'));
    }

    public function testReadEmpty()
    {
        $redisMock = $this->getMock('Redis');
        $redisMock->method('exists')
            ->with($this->equalTo('session1'))
            ->will($this->returnValue(false));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertNull($s->read('session1'));
    }

    public function testReadError()
    {
        $redisMock = $this->getMock('Redis');
        $redisMock->method('exists')
            ->with($this->equalTo('session2'))
            ->will($this->throwException(new RedisException));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertNull($s->read('session2'));
    }

    public function testRead()
    {
        $redisMock = $this->getMock('Redis');
        $redisMock->method('exists')
            ->with($this->equalTo('session3'))
            ->will($this->returnValue(true));
        $redisMock->method('expire')
            ->with($this->equalTo('session3'), $this->equalTo(10));
        $redisMock->method('get')
            ->with($this->equalTo('session3'))
            ->will($this->returnValue('session_data'));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertEquals('session_data', $s->read('session3'));
    }

    public function testDestroy()
    {
        $redisMock = $this->getMock('Redis');
        $redisMock->method('del')
            ->with($this->equalTo('session4'))
            ->will($this->returnValue(1));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertTrue($s->destroy('session4'));
    }

    public function testDestroyEmpty()
    {
        $redisMock = $this->getMock('Redis');
        $redisMock->method('del')
            ->with($this->equalTo('session5'))
            ->will($this->returnValue(0));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertFalse($s->destroy('session5'));
    }

    public function testDestroyError()
    {
        $redisMock = $this->getMock('Redis');
        $redisMock->method('del')
            ->with($this->equalTo('session6'))
            ->will($this->throwException(new RedisException));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertFalse($s->destroy('session6'));
    }
}