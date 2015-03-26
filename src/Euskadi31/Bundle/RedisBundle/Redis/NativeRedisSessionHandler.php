<?php
/*
 * This file is part of the RedisBundle package.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Bundle\RedisBundle\Redis;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
use Redis;

/**
 * NativeRedisSessionStorage.
 *
 * Driver for the redis session save handler provided by the redis PHP extension.
 *
 * @see https://github.com/nicolasff/phpredis
 *
 * @author Axel Etcheverry <axel@etcheverry.biz>
 */
class NativeRedisSessionHandler extends NativeSessionHandler
{
    /**
     * Constructor.
     *
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        ini_set('session.save_handler', 'redis');
        ini_set(
            'session.save_path',
            sprintf('tcp://%s:%d?persistent=0', $redis->getHost(), $redis->getPort())
        );
    }
}
