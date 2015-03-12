<?php
/*
 * This file is part of the RedisBundle package.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Bundle\RedisBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Redis;

class RedisDebugCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('redis:debug')
            ->setDescription('Displays current Redis config.')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication();

        $config = $this->getContainer()->getParameter('redis');
        $table = $app->getHelperSet()->get('table');
        $redisManager = $this->getContainer()->get('redis.manager');

        if ($config['type'] == 'sentinel') {
            $this->displaySentinelTable($table, $config, $redisManager, $output);
        } else {
            $this->displayRedisTable($table, $config, $redisManager, $output);
        }
    }


    protected function displayRedisTable($table, $config, $redisManager, $output)
    {
        $header = ['Host', 'Port', 'Db', 'Timeout', 'Namespace', 'Status'];
        $data = [];

        $data[] = [
            $config['server']['host'],
            $config['server']['port'],
            $config['client']['redis']['db'],
            $config['client']['redis']['timeout'],
            $config['client']['redis']['namespace'],
            $redisManager->getRedis()->isConnected() ? '<info>Online</info>' : '<error>Offline</error>'
        ];

        $table->setHeaders($header)->setRows($data);
        $table->render($output);
    }

    protected function displaySentinelTable($table, $config, $redisManager, $output)
    {
        $header = ['Host', 'Port', 'Timeout', 'Status'];
        $data = [];

        foreach ($config['sentinels'] as $sentinel) {
            $data[] = [
                $sentinel['host'],
                $sentinel['port'],
                $config['client']['sentinel']['timeout'],
                (new Redis())->connect(
                    $sentinel['host'],
                    $sentinel['port'],
                    $config['client']['sentinel']['timeout']
                ) ? '<info>Online</info>' : '<error>Offline</error>'
            ];
        }

        $table->setHeaders($header)->setRows($data);
        $table->render($output);

        $info = $redisManager->getMasterDiscovery()->getMasterAddrByName(
            $config['client']['sentinel']['master']
        );

        $config['server'] = [
            'host' => $info[0],
            'port' => $info[1]
        ];

        $this->displayRedisTable($table, $config, $redisManager, $output);
    }
}
