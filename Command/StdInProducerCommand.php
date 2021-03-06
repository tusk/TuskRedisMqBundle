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

class StdInProducerCommand extends ContainerAwareCommand
{
    protected $abort = false;

    protected function configure()
    {
        $this
            ->setName('tusk-redis-mq:stdin-producer')
            ->setDescription('TuskRedisMq STDIN producer command')
            ->addArgument('producer', InputArgument::REQUIRED, 'Producer name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $producer = $this->getContainer()->get(
            sprintf(
                'tusk_redis_mq.producer.%s',
                $input->getArgument('producer')
            )
        );
        $data = '';
        while (! feof(STDIN)) {
            $data .= fread(STDIN, 8192);
        }
        $producer->publish($data);
    }
}
