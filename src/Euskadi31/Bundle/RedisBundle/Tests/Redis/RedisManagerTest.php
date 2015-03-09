<?php

namespace Euskadi31\Bundle\RedisBundle\Tests\Redis;

use Euskadi31\Bundle\RedisBundle\Redis\RedisManager;
use RedisException;
use Redis;

class RedisManagerTest extends \PHPUnit_Framework_TestCase
{
    public function getRedisMock()
    {
        $redisMock = $this->getMock('Redis');
        $redisMock->method('setOption')
            ->with(
                $this->equalTo(Redis::OPT_PREFIX),
                $this->equalTo('Euskadi31_Bundle_RedisBundle_Redis_RedisSessionHandler__')
            );

        return $redisMock;
    }

    /**
     * @expectedException Euskadi31\Bundle\RedisBundle\Redis\RedisManagerException
     * @expectedExceptionMessage Redis is already connected
     * @codeCoverageIgnore
     */
    public function testConstructorWithRedisConnected()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('isConnected')
            ->will($this->returnValue(true));

        $manager = new RedisManager($redisMock, [
            'client' => [
                'redis' => [
                    'timeout' => 1
                ]
            ]
        ]);
    }

    public function testConstructorWithRedisConf()
    {
        $redisMock = $this->getRedisMock();

        $manager = new RedisManager($redisMock, [
            'client' => [
                'redis' => [
                    'timeout' => 1
                ]
            ]
        ]);

        $this->assertInstanceOf('Redis', $manager->getRedis());
        $this->assertEquals($redisMock, $manager->getRedis());
    }

    public function testConstructorWithSentinelConf()
    {
        $redisMock = $this->getRedisMock();

        $manager = new RedisManager($redisMock, [
            'client' => [
                'sentinel' => [
                    'timeout' => 0.5
                ]
            ]
        ]);

        $this->assertInstanceOf('Redis', $manager->getRedis());
        $this->assertEquals($redisMock, $manager->getRedis());
    }

    /**
     * @expectedException Euskadi31\Bundle\RedisBundle\Redis\RedisManagerException
     * @expectedExceptionMessage Bad config
     * @codeCoverageIgnore
     */
    public function testConstructorWithBadConf()
    {
        $redisMock = $this->getRedisMock();

        $manager = new RedisManager($redisMock, []);
    }
}
