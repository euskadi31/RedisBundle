<?php

namespace Euskadi31\Bundle\RedisBundle\Redis;

use SessionHandlerInterface;
use Redis;

/**
 * Session handler
 */
class RedisSessionHandler implements SessionHandlerInterface
{
    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var integer
     */
    protected $max_lifetime;

    /**
     * @var string
     */
    protected $prefix;

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
        $this->prefix = 'symfony:session:';
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
            if (!$this->redis->exists($this->prefix . $session_id)) {
                return null;
            } else {
                // each read increment lifetime
                $this->redis->expire($this->prefix . $session_id, $this->max_lifetime);
                return $this->redis->get($this->prefix . $session_id);
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
            $this->redis->set($this->prefix . $session_id, (string)$data, $this->max_lifetime);
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
            $ret = $this->redis->del($this->prefix . $session_id);

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
