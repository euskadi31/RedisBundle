<?php

namespace Euskadi31\Bundle\RedisBundle\Redis;

use Redis;

interface RedisManagerInterface
{
    public function __construct(array $config, Redis $redis = null);

    /**
     * Get Redis instance
     *
     * @return Redis
     */
    public function getRedis();
}
