<?php
/*
 * This file is part of the RedisBundle package.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Bundle\RedisBundle\Tests\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Euskadi31\Bundle\RedisBundle\DependencyInjection\Euskadi31RedisExtension;

class Euskadi31RedisExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Euskadi31RedisExtension
     */
    protected $extension;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     *
     */
    protected function initContainer()
    {
        $this->extension = new Euskadi31RedisExtension();
        $this->container = new ContainerBuilder();
        $this->container->register('event_dispatcher', new EventDispatcher());
        $this->container->registerExtension($this->extension);
        $this->container->setParameter('kernel.debug', true);
    }

    /**
     * @param ContainerBuilder $container
     * @param $resource
     */
    protected function loadConfiguration(ContainerBuilder $container, $resource)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../_files/'));
        $loader->load($resource . '.yml');
    }

    public function testConfig()
    {

    }

    public function testRedisConfig()
    {
        $this->initContainer();
        $this->loadConfiguration($this->container, 'redis_config');

        $this->container->compile();

        $this->assertTrue($this->container->has('redis'));
        $redis = $this->container->get('redis');
        $this->assertInstanceOf('Redis', $redis);

        $this->assertTrue($this->container->has('redis.manager'));
        $manager = $this->container->get('redis.manager');
        $this->assertInstanceOf('Euskadi31\Bundle\RedisBundle\Redis\RedisManager', $manager);

        $this->assertEquals($redis, $manager->getRedis());
    }

    public function testSentinelsConfig()
    {
        $this->initContainer();
        $this->loadConfiguration($this->container, 'sentinel_config');

        $this->container->compile();

        $this->assertTrue($this->container->has('redis'));
        $redis = $this->container->get('redis');
        $this->assertInstanceOf('Redis', $redis);

        $this->assertTrue($this->container->has('redis.manager'));
        $manager = $this->container->get('redis.manager');
        $this->assertInstanceOf('Euskadi31\Bundle\RedisBundle\Redis\RedisManager', $manager);

        $this->assertEquals($redis, $manager->getRedis());
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The path "euskadi31_redis.sentinels" should have at least 1 element(s) defined.
     * @codeCoverageIgnore
     */
    public function testBadSentinelsConfig()
    {
        $this->initContainer();
        $this->loadConfiguration($this->container, 'bad_sentinel_config');

        $this->container->compile();
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The path "euskadi31_redis.sentinels" should have at least 1 element(s) defined.
     * @codeCoverageIgnore
     */
    public function testBadConfig()
    {
        $this->initContainer();
        $this->loadConfiguration($this->container, 'bad_config');

        $this->container->compile();
    }
}
