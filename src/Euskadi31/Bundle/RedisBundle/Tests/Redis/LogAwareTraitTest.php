<?php
/*
 * This file is part of the RedisBundle package.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Bundle\RedisBundle\Tests\Redis;

use Euskadi31\Bundle\RedisBundle\Redis\LogAwareTrait;
use Psr\Log\LogLevel;

class LogAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testLog()
    {
        $trait = $this->getObjectForTrait('Euskadi31\Bundle\RedisBundle\Redis\LogAwareTrait');

        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $loggerMock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(LogLevel::INFO), $this->equalTo('foo'), $this->equalTo(['bar']));

        $trait->setLogger($loggerMock);

        $trait->log(LogLevel::INFO, 'foo', ['bar']);
    }

    public function testLogEmpty()
    {
        $trait = $this->getObjectForTrait('Euskadi31\Bundle\RedisBundle\Redis\LogAwareTrait');

        $trait->log(LogLevel::INFO, 'foo', ['bar']);
    }
}
