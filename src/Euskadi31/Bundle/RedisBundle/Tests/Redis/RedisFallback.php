<?php

class RedisMasterDiscovery
{
    protected $sentinels = [];

    public function addSentinel(RedisSentinel $sentinel)
    {
        $this->sentinels[] = $sentinel;
    }

    public function getSentinels()
    {
        return $this->sentinels;
    }

    public function getMasterAddrByName($master)
    {
        return ['127.0.0.1', '6379'];
    }
}


class RedisSentinel
{
    public function __construct($host, $port)
    {

    }
}
