<?php

/*
 * This file is part of the Tusk RedisQueueBundle package.
 *
 * (c) 2012 Tusk PHP Components <frizzy@paperjaw.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tusk\RedisQueueBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class StdInOutRpcCommand extends ContainerAwareCommand
{
    protected $abort = false;

    protected function configure()
    {
        $this
            ->setName('tusk-redis-queue:stdin-stdout-rpc')
            ->setDescription('TuskRedisQueue STDIN/STDOUT RPC command')
            ->addArgument('rpc', InputArgument::REQUIRED, 'RPC handler name')
            ->addArgument('channel', InputArgument::REQUIRED, 'RPC channel');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rpcFactory = $this->getContainer()->get(
            sprintf(
                'tusk_redis_queue.rpc.%s',
                $input->getArgument('rpc')
            )
        );
        $rpc = $rpcFactory->get();
        $data = '';
        while (! feof(STDIN)) {
            $data .= fread(STDIN, 8192);
        }
        $rpc->addRequest($input->getArgument('channel'), $data);
        $responses = $rpc->getResponses();
        foreach ($responses as $response) {
            $output->write($response);
        }
    }
}
