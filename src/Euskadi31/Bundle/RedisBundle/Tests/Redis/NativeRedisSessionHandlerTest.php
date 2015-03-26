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

use Euskadi31\Bundle\RedisBundle\Redis\NativeRedisSessionHandler;

class NativeRedisSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $redisMock = $this->getMock('Redis');
        $redisMock->expects($this->once())
            ->method('getHost')
            ->will($this->returnValue('127.0.0.1'));
        $redisMock->expects($this->once())
            ->method('getPort')
            ->will($this->returnValue('6379'));

        $handler = new NativeRedisSessionHandler($redisMock);

        $this->assertInstanceOf(
            '\Euskadi31\Bundle\RedisBundle\Redis\NativeRedisSessionHandler',
            $handler
        );

        $this->assertEquals('tcp://127.0.0.1:6379?persistent=0', ini_get('session.save_path'));
    }
}
