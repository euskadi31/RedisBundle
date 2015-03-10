<?php

namespace Euskadi31\Bundle\RedisBundle\Redis;

use Redis;
use RedisMasterDiscovery;
use RedisSentinel;

class RedisManager implements RedisManagerInterface
{
    /**
     * @var Redis
     */
    protected $redis;

    /**
     *
     */
    public function __construct(array $config, Redis $redis = null)
    {
        if (is_null($redis)) {
            $redis = new Redis();
        }

        $this->redis = $redis;

        if ($redis->isConnected()) {
            throw new RedisManagerException('Redis is already connected');
        }

        $this->processConfig($config);
    }

    /**
     * Process redis config
     *
     * @param  array  $config
     * @return void
     */
    public function processConfig(array $config)
    {
        if (isset($config['client']['redis'])) {
            $this->processRedisConfig($config);
        } else if (isset($config['client']['sentinel'])) {
            $this->processSentinelConfig($config);
        } else {
            throw new RedisManagerException('Bad config');
        }
    }

    /**
     * @param  array  $config
     * @return void
     */
    public function processRedisConfig(array $config)
    {
        if (!isset($config['server'])) {
            throw new RedisManagerException('The "server" property is required.');
        }

        $conf = $config['client']['redis'];
        $conf['host'] = $config['server']['host'];

        if (isset($config['server']['port'])) {
            $conf['port'] = $config['server']['port'];
        }

        $this->connect($conf);
    }

    /**
     *
     * @param  array  $config
     * @return void
     */
    public function connect(array $config)
    {
        if ($config['host'][0] == '/') {
            $this->redis->connect($config['host']);
        } else {
            $this->redis->connect(
                $config['host'],
                $config['port'],
                $config['timeout']
            );
        }

        if (isset($config['auth']) && !empty($config['auth'])) {
            $this->redis->auth($config['auth']);
        }

        if (isset($config['db']) && !is_null($config['db'])) {
            $this->redis->select((int)$config['db']);
        }

        if (isset($config['namespace']) && !empty($config['namespace'])) {
            $this->redis->setOption(
                Redis::OPT_PREFIX,
                rtrim($config['namespace'], ':') . ':'
            );
        }
    }

    /**
     *
     * @param  array  $config
     * @return void
     */
    public function processSentinelConfig(array $config)
    {
        if (!isset($config['sentinels'])) {
            throw new RedisManagerException('The "sentinels" property is required.');
        }

        $discovery = new RedisMasterDiscovery();

        foreach ($config['sentinels'] as $sentinel) {
            $discovery->addSentinel(new RedisSentinel($sentinel['host'], $sentinel['port']));
        }

        $master = $discovery->getMasterAddrByName($config['client']['sentinel']['master']);

        $conf = $config['client']['sentinel'];
        $conf['host'] = $master[0];
        $conf['port'] = $master[1];

        $this->connect($conf);
    }


    /**
     * {@inheritDoc}
     */
    public function getRedis()
    {
        return $this->redis;
    }
}
