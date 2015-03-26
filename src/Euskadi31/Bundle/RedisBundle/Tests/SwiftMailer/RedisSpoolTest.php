<?php
/*
 * This file is part of the RedisBundle package.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Bundle\RedisBundle\Tests\SwiftMailer;

use Euskadi31\Bundle\RedisBundle\SwiftMailer\RedisSpool;

class RedisSpoolTest extends \PHPUnit_Framework_TestCase
{
    public function getRedisMock()
    {
        $redisMock = $this->getMock('Redis');

        return $redisMock;
    }

    public function testRedis()
    {
        $redisMock = $this->getRedisMock();

        $spool = new RedisSpool();
        $spool->setRedis($redisMock);
        $this->assertEquals($redisMock, $spool->getRedis());
    }

    public function testKey()
    {
        $spool = new RedisSpool();
        $spool->setKey('foo');
        $this->assertEquals('foo', $spool->getKey());
    }

    public function testStart()
    {
        $spool = new RedisSpool();
        $spool->start();
    }

    public function testStop()
    {
        $spool = new RedisSpool();
        $spool->stop();
    }

    public function testIsStarted()
    {
        $spool = new RedisSpool();
        $this->assertTrue($spool->isStarted());
    }

    public function testQueueMessage()
    {
        $redisMock = $this->getRedisMock();
        $messageMock = $this->getMock('Swift_Mime_Message');

        $redisMock->expects($this->once())
            ->method('rpush')
            ->with($this->equalTo('foo'), $this->equalTo(serialize($messageMock)))
            ->will($this->returnValue(1));

        $spool = new RedisSpool();
        $spool->setKey('foo');
        $spool->setRedis($redisMock);
        $this->assertTrue($spool->queueMessage($messageMock));
    }

    public function testFlushQueueEmpty()
    {
        $redisMock = $this->getRedisMock();
        $transportMock = $this->getMock('Swift_Transport');

        $redisMock->expects($this->once())
            ->method('llen')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(0));

        $spool = new RedisSpool();
        $spool->setKey('foo');
        $spool->setRedis($redisMock);
        $this->assertEquals(0, $spool->flushQueue($transportMock));
    }

    public function testFlushQueue()
    {
        $redisMock = $this->getRedisMock();
        $transportMock = $this->getMock('Swift_Transport');
        $messageMock = $this->getMock('Swift_Mime_Message');

        $transportMock->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));

        $transportMock->expects($this->once())
            ->method('start');

        $transportMock->expects($this->exactly(2))
            ->method('send')
            ->with($this->equalTo($messageMock), $this->equalTo([]))
            ->will($this->returnValue(1));

        $redisMock->expects($this->once())
            ->method('llen')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(2));

        $list = [
            serialize($messageMock),
            serialize($messageMock)
        ];

        $redisMock->expects($this->exactly(3))
            ->method('lpop')
            ->with($this->equalTo('foo'))
            ->will($this->returnCallback(function($arg) use (&$list) {
                return array_shift($list);
            }));

        $spool = new RedisSpool();
        $spool->setKey('foo');
        $spool->setRedis($redisMock);
        $this->assertEquals(2, $spool->flushQueue($transportMock));

    }

    public function testFlushQueueWithMessageLimit()
    {
        $redisMock = $this->getRedisMock();
        $transportMock = $this->getMock('Swift_Transport');
        $messageMock = $this->getMock('Swift_Mime_Message');

        $transportMock->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));

        $transportMock->expects($this->once())
            ->method('start');

        $transportMock->expects($this->exactly(1))
            ->method('send')
            ->with($this->equalTo($messageMock), $this->equalTo([]))
            ->will($this->returnValue(1));

        $redisMock->expects($this->once())
            ->method('llen')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(2));

        $list = [
            serialize($messageMock),
            serialize($messageMock)
        ];

        $redisMock->expects($this->exactly(1))
            ->method('lpop')
            ->with($this->equalTo('foo'))
            ->will($this->returnCallback(function($arg) use (&$list) {
                return array_shift($list);
            }));

        $spool = new RedisSpool();
        $spool->setMessageLimit(1);
        $spool->setKey('foo');
        $spool->setRedis($redisMock);
        $this->assertEquals(1, $spool->flushQueue($transportMock));
    }

    public function testFlushQueueWithTimeLimit()
    {
        $redisMock = $this->getRedisMock();
        $transportMock = $this->getMock('Swift_Transport');
        $messageMock = $this->getMock('Swift_Mime_Message');

        $transportMock->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));

        $transportMock->expects($this->once())
            ->method('start');

        $transportMock->expects($this->exactly(1))
            ->method('send')
            ->with($this->equalTo($messageMock), $this->equalTo([]))
            ->will($this->returnValue(1));

        $redisMock->expects($this->once())
            ->method('llen')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(2));

        $list = [
            serialize($messageMock),
            serialize($messageMock)
        ];

        $redisMock->expects($this->exactly(1))
            ->method('lpop')
            ->with($this->equalTo('foo'))
            ->will($this->returnCallback(function($arg) use (&$list) {
                sleep(1);
                return array_shift($list);
            }));

        $spool = new RedisSpool();
        $spool->setTimeLimit(1);
        $spool->setKey('foo');
        $spool->setRedis($redisMock);
        $this->assertEquals(1, $spool->flushQueue($transportMock));
    }

}
