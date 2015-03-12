<?php
/*
 * This file is part of the RedisBundle package.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Bundle\RedisBundle\Tests\Redis;

use Euskadi31\Bundle\RedisBundle\Redis\RedisSessionHandler;
use RedisException;
use Redis;

class RedisSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function getRedisMock()
    {
        $redisMock = $this->getMock('Redis');
        /*$redisMock->method('setOption')
            ->with(
                $this->equalTo(Redis::OPT_PREFIX),
                $this->equalTo('Euskadi31_Bundle_RedisBundle_Redis_RedisSessionHandler__')
            );*/

        return $redisMock;
    }

    public function testConstructor()
    {
        $redisMock = $this->getRedisMock();

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertInstanceOf('Redis', $s->getRedis());
    }

    public function testOpen()
    {
        $redisMock = $this->getRedisMock();

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertTrue($s->open('/', 'toto'));
    }

    public function testClose()
    {
        $redisMock = $this->getRedisMock();

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertTrue($s->close());
    }

    public function testGc()
    {
        $redisMock = $this->getRedisMock();

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertTrue($s->gc(10));
    }

    public function testWrite()
    {
        $redisMock = $this->getRedisMock();

        $redisMock->method('set')
            ->with($this->equalTo('symfony:session:foo'), $this->equalTo('bar'), $this->equalTo(11));

        $s = new RedisSessionHandler($redisMock, 11);

        $this->assertTrue($s->write('foo', 'bar'));
    }

    public function testWriteError()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('set')
            ->with($this->equalTo('symfony:session:foo1'), $this->equalTo('bar1'), $this->equalTo(10))
            ->will($this->throwException(new RedisException));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertFalse($s->write('foo1', 'bar1'));
    }

    public function testReadEmpty()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('exists')
            ->with($this->equalTo('symfony:session:session1'))
            ->will($this->returnValue(false));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertNull($s->read('session1'));
    }

    public function testReadError()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('exists')
            ->with($this->equalTo('symfony:session:session2'))
            ->will($this->throwException(new RedisException));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertNull($s->read('session2'));
    }

    public function testRead()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('exists')
            ->with($this->equalTo('symfony:session:session3'))
            ->will($this->returnValue(true));
        $redisMock->method('expire')
            ->with($this->equalTo('symfony:session:session3'), $this->equalTo(10));
        $redisMock->method('get')
            ->with($this->equalTo('symfony:session:session3'))
            ->will($this->returnValue('session_data'));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertEquals('session_data', $s->read('session3'));
    }

    public function testDestroy()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('del')
            ->with($this->equalTo('symfony:session:session4'))
            ->will($this->returnValue(1));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertTrue($s->destroy('session4'));
    }

    public function testDestroyEmpty()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('del')
            ->with($this->equalTo('symfony:session:session5'))
            ->will($this->returnValue(0));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertFalse($s->destroy('session5'));
    }

    public function testDestroyError()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('del')
            ->with($this->equalTo('symfony:session:session6'))
            ->will($this->throwException(new RedisException));

        $s = new RedisSessionHandler($redisMock, 10);

        $this->assertFalse($s->destroy('session6'));
    }
}
