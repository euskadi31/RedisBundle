<?php

namespace Euskadi31\Bundle\RedisBundle\Redis;

use Redis;

class RedisManager implements RedisManagerInterface
{
    /**
     * @var Redis
     */
    protected $redis;

    /**
     *
     */
    public function __construct(Redis $redis, array $config)
    {
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
     * [processRedisConfig description]
     * @param  array  $config [description]
     * @return [type]         [description]
     */
    public function processRedisConfig(array $config)
    {
        if (!isset($config['server'])) {
            throw new RedisManagerException('The "server" property is required.');
        }

        if ($config['server']['host'][0] == '/') {
            $this->redis->connect($config['server']['host']);
        } else {
            $this->redis->connect(
                $config['server']['host'],
                $config['server']['port'],
                $config['client']['redis']['timeout']
            );
        }

        if (isset($config['client']['redis']['auth']) && !empty($config['client']['redis']['auth'])) {
            $this->redis->auth($config['client']['redis']['auth']);
        }

        if (isset($config['client']['redis']['db']) && !is_null($config['client']['redis']['db'])) {
            $this->redis->select((int)$config['client']['redis']['db']);
        }

        if (isset($config['client']['redis']['namespace']) && !empty($client['client']['redis']['namespace'])) {
            $this->redis->setOption(
                Redis::OPT_PREFIX,
                rtrim($config['client']['redis']['namespace'], ':') . ':'
            );
        }
    }

    /**
     * [processSentinelConfig description]
     * @param  array  $config [description]
     * @return [type]         [description]
     */
    public function processSentinelConfig(array $config)
    {
        if (!isset($config['sentinels'])) {
            throw new RedisManagerException('The "sentinels" property is required.');
        }
    }


    /**
     * {@inheritDoc}
     */
    public function getRedis()
    {
        return $this->redis;
    }
}
