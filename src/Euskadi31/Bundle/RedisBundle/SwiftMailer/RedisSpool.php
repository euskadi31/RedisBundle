<?php
/*
 * This file is part of the RedisBundle package.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Bundle\RedisBundle\SwiftMailer;

use Swift_ConfigurableSpool;
use Swift_Mime_Message;
use Swift_Transport;
use Redis;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use Euskadi31\Bundle\RedisBundle\Redis\LogAwareTrait;

/**
 * RedisSpool
 */
class RedisSpool extends Swift_ConfigurableSpool implements LoggerAwareInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var \Redis
     */
    protected $redis;

    use LogAwareTrait;

    /**
     * @param \Redis $redis
     */
    public function setRedis(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Get Redis
     *
     * @return \Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function queueMessage(Swift_Mime_Message $message)
    {
        $length = $this->redis->rpush($this->key, serialize($message));

        $this->log(LogLevel::DEBUG, 'queue message', [$this->key, $message]);

        return (bool) $length;
    }

    /**
     * {@inheritdoc}
     */
    public function flushQueue(Swift_Transport $transport, &$failedRecipients = null)
    {
        if (!$this->redis->llen($this->key)) {
            return 0;
        }

        if (!$transport->isStarted()) {
            $transport->start();
        }

        $failedRecipients   = (array)$failedRecipients;
        $count              = 0;
        $time               = time();

        while (($message = unserialize($this->redis->lpop($this->key)))) {
            $this->log(LogLevel::DEBUG, 'send message', [$message]);

            $count += $transport->send($message, $failedRecipients);

            if ($this->getMessageLimit() && $count >= $this->getMessageLimit()) {
                break;
            }

            if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit()) {
                break;
            }
        }

        return $count;
    }
}
