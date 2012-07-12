<?php

/*
 * This file is part of the Tusk RedisMqBundle package.
 *
 * (c) 2012 Tusk PHP Components <frizzy@paperjaw.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tusk\RedisMqBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Tusk\RedisMq\Monitor;

class MonitorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tusk-redis-mq:monitor')
            ->setDescription('TuskRedisMq monitor command')
            ->addArgument('connection', InputArgument::REQUIRED, 'Connection')
            ->addArgument('channel', InputArgument::OPTIONAL, 'Channel')
            ->addOption('rpc', 'r', InputOption::VALUE_OPTIONAL, 'Include RPC channels', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getContainer()->get(
            sprintf(
                'tusk_redis_mq.connection.%s',
                $input->getArgument('connection')
            )
        );

        $monitor = new Monitor($connection);

        if (null !== $channel = $input->getArgument('channel')) {
            $output->writeLn($monitor->getChannelQueueLength($channel));
        } else {
            $includeRpcChannel = $input->getOption('rpc') !== false;
            $channels = $monitor->getChannels($includeRpcChannel);
            sort($channels);
            if (count($channels) > 0) {
                $output->writeLn('Available channels:');
            } else {
                $output->writeLn('No channels');
            }
            foreach ($channels as $channel) {
                $output->writeLn(
                    sprintf(
                        ' %s %d',
                        str_pad($channel, 24, ' ', STR_PAD_RIGHT),
                        str_pad(
                            $monitor->getChannelQueueLength($channel),
                            7,
                            ' ',
                            STR_PAD_LEFT
                        )
                    )
                );
            }
        }
    }
}
