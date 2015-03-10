<?php

namespace Euskadi31\Bundle\RedisBundle\Tests\Redis;

if (!class_exists('RedisMasterDiscovery')) {
    require __DIR__ . '/RedisFallback.php';
}

use Euskadi31\Bundle\RedisBundle\Redis\RedisManager;
use RedisException;
use Redis;

class RedisManagerTest extends \PHPUnit_Framework_TestCase
{
    public function getRedisMock()
    {
        $redisMock = $this->getMock('Redis');

        return $redisMock;
    }

    public function testConstructorWithoutRedis()
    {
        $manager = new RedisManager([
            'server' => [
                'host' => '127.0.0.1',
                'port' => 6379
            ],
            'client' => [
                'redis' => [
                    'timeout' => 1
                ]
            ]
        ]);

        $this->assertInstanceOf('Redis', $manager->getRedis());
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

        $manager = new RedisManager([
            'client' => [
                'redis' => [
                    'timeout' => 1
                ]
            ]
        ], $redisMock);
    }

    public function testConstructorWithRedisConf()
    {
        $redisMock = $this->getRedisMock();

        $manager = new RedisManager([
            'server' => [
                'host' => '127.0.0.1',
                'port' => 6379
            ],
            'client' => [
                'redis' => [
                    'timeout' => 1
                ]
            ]
        ], $redisMock);

        $this->assertInstanceOf('Redis', $manager->getRedis());
        $this->assertEquals($redisMock, $manager->getRedis());
    }

    public function testConstructorWithRedisConfSocket()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('connect')
            ->with($this->equalTo('/var/run/redis.sock'));

        $manager = new RedisManager([
            'server' => [
                'host' => '/var/run/redis.sock'
            ],
            'client' => [
                'redis' => [
                    'timeout' => 1
                ]
            ]
        ], $redisMock);
    }

    public function testConstructorWithRedisConfAuth()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('auth')
            ->with($this->equalTo('password'));

        $manager = new RedisManager([
            'server' => [
                'host' => '/var/run/redis.sock'
            ],
            'client' => [
                'redis' => [
                    'timeout' => 1,
                    'auth' => 'password'
                ]
            ]
        ], $redisMock);
    }

    public function testConstructorWithRedisConfDb()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('select')
            ->with($this->equalTo(4));

        $manager = new RedisManager([
            'server' => [
                'host' => '/var/run/redis.sock'
            ],
            'client' => [
                'redis' => [
                    'timeout' => 1,
                    'db' => 4
                ]
            ]
        ], $redisMock);
    }

    public function testConstructorWithRedisConfNamespace()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('setOption')
            ->with($this->equalTo(Redis::OPT_PREFIX), $this->equalTo('my:app:'));

        $manager = new RedisManager([
            'server' => [
                'host' => '/var/run/redis.sock'
            ],
            'client' => [
                'redis' => [
                    'timeout' => 1,
                    'namespace' => 'my:app:'
                ]
            ]
        ], $redisMock);
    }

    /**
     * @expectedException Euskadi31\Bundle\RedisBundle\Redis\RedisManagerException
     * @expectedExceptionMessage The "server" property is required.
     * @codeCoverageIgnore
     */
    public function testConstructorWithBadRedisConf()
    {
        $redisMock = $this->getRedisMock();

        $manager = new RedisManager([
            'client' => [
                'redis' => [
                    'timeout' => 1
                ]
            ]
        ], $redisMock);
    }

    public function testConstructorWithSentinelConf()
    {
        $redisMock = $this->getRedisMock();
        $redisMock->method('connect')
            ->with($this->equalTo('127.0.0.1'), $this->equalTo(6379), $this->equalTo(0.5));


        $manager = new RedisManager([
            'sentinels' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 26379
                ]
            ],
            'client' => [
                'sentinel' => [
                    'master' => 'mymaster',
                    'timeout' => 0.5
                ]
            ]
        ], $redisMock);

        $this->assertInstanceOf('Redis', $manager->getRedis());
        $this->assertEquals($redisMock, $manager->getRedis());
    }

    /**
     * @expectedException Euskadi31\Bundle\RedisBundle\Redis\RedisManagerException
     * @expectedExceptionMessage The "sentinels" property is required.
     * @codeCoverageIgnore
     */
    public function testConstructorWithBadSentinelConf()
    {
        $redisMock = $this->getRedisMock();

        $manager = new RedisManager([
            'client' => [
                'sentinel' => [
                    'master' => 'mymaster',
                    'timeout' => 0.5
                ]
            ]
        ], $redisMock);
    }

    /**
     * @expectedException Euskadi31\Bundle\RedisBundle\Redis\RedisManagerException
     * @expectedExceptionMessage Bad config
     * @codeCoverageIgnore
     */
    public function testConstructorWithBadConf()
    {
        $redisMock = $this->getRedisMock();

        $manager = new RedisManager([], $redisMock);
    }
}
