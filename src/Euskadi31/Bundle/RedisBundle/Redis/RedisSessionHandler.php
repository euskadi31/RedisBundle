<?php

namespace Euskadi31\Bundle\RedisBundle\Redis;

use SessionHandlerInterface;
use Redis;

/**
 * Session handler
 */
class RedisSessionHandler implements SessionHandlerInterface
{
    protected $redis;

    protected $max_lifetime;

    /**
     * Contructor
     *
     * @param Redis     $manager        instance of RedisManager
     * @param integer   $max_lifetime   max lifetime of Redis storage
     */
    public function __construct(Redis $redis, $max_lifetime)
    {
        $this->max_lifetime = $max_lifetime;
        $this->redis = clone $redis;
        $this->redis->setOption(Redis::OPT_PREFIX, str_replace('\\', '_', __CLASS__) . '__');
    }

    /**
     * Getter for the redis object
     *
     * @return Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * {@inheritDoc}
     */
    public function open($save_path, $session_name)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($session_id)
    {
        try {
            if (!$this->redis->exists($session_id)) {
                return null;
            } else {
                // each read increment lifetime
                $this->redis->expire($session_id, $this->max_lifetime);
                return $this->redis->get($session_id);
            }
        } catch (\RedisException $e) {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write($session_id, $data)
    {
        try {
            $this->redis->set($session_id, (string)$data, $this->max_lifetime);
            return true;
        } catch (\RedisException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($session_id)
    {
        try {
            $ret = $this->redis->del($session_id);

            return ($ret > 0);

        } catch (\RedisException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function gc($lifetime)
    {
        return true;
    }
}
