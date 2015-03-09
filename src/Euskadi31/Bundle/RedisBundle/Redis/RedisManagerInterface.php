<?php

namespace Euskadi31\Bundle\RedisBundle\Redis;

use Redis;

interface RedisManagerInterface
{
    public function __construct(Redis $redis, array $config);

    /**
     * Get Redis instance
     *
     * @return Redis
     */
    public function getRedis();
}
