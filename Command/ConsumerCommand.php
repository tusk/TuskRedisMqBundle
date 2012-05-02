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

class ConsumerCommand extends ContainerAwareCommand
{
    protected $abort  = false;
    protected $paused = false;

    protected function configure()
    {
        $this
            ->setName('tusk-redis-mq:consumer')
            ->setDescription('TuskRedisMq consumer command')
            ->addArgument('consumer', InputArgument::REQUIRED, 'Consumer name')
            ->addOption('messages', 'm', InputOption::VALUE_OPTIONAL, 'Messages to consume', 0)
            ->addOption('listen', 'l', InputOption::VALUE_OPTIONAL, 'Listen timeout in seconds', 10)
            ->addOption('paused', 'p', InputOption::VALUE_OPTIONAL, 'Start in paused mode. Send SIGCONT to start', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->paused = null !== $input->getOption('paused');
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGINT, array($this, 'trapSignal'));
            pcntl_signal(SIGTERM, array($this, 'trapSignal'));
            pcntl_signal(SIGCONT, array($this, 'trapSignal'));
            pcntl_signal(SIGSTOP, array($this, 'trapSignal'));
        }
        $messages = $input->getOption('messages');
        $consumer = $this->getContainer()->get(
            sprintf(
                'tusk_redis_mq.consumer.%s',
                $input->getArgument('consumer')
            )
        );
        $messagesConsumed = 0;
        while ($this->checkStatus()) {
            if ($consumer->listen($input->getOption('listen'))) {
                $messagesConsumed++;
                if (! $input->getOption('quiet')) {
                    $output->writeLn(
                        sprintf(
                            'Message consumed %d',
                            $messagesConsumed
                        )
                    );
                }
            }
            if ($messages != 0 && $messagesConsumed >= $messages) {
                break;
            }
        }
    }

    public function trapSignal($signal)
    {
        switch ($signal) {
        case SIGINT:
        case SIGTERM:
            echo 'Signal received. Ending process' . PHP_EOL;
            $this->abort = true;
            break;
        case SIGCONT:
            $this->paused = false;
            break;
        case SIGSTOP:
            $this->paused = true;
        }
    }

    private function checkStatus()
    {
        if ($this->paused) {
            while (true) {
                if ($this->abort) {
                    return false;
                }
                sleep(1);
                if (false === $this->paused) {
                    break;
                }
            }
        }
        if ($this->abort) {
            return false;
        }

        return true;
    }
}
