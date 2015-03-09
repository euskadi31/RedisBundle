<?php

class RedisMasterDiscovery
{
    public function addSentinel(RedisSentinel $sentinel)
    {

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
