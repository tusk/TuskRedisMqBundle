<?php

/*
 * This file is part of the Tusk RedisMqBundle package.
 *
 * (c) 2012 Tusk PHP Components <frizzy@paperjaw.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tusk\RedisMqBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TuskRedisMqExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');
        $this->loadConnections($config['connections'], $container);
        $this->loadProducers($config['producers'], $container);
        $this->loadConsumers($config['consumers'], $container);
        $this->loadRpc($config['rpc'], $container);
    }

    private function loadConnections(array $connections, ContainerBuilder $container)
    {
        $class = $container->getParameter('tusk_redis_mq.connection.class');
        $monitorClass = $container->getParameter('tusk_redis_mq.monitor.class');
        foreach ($connections as $name => $connection) {
            $definition = new Definition($class);
            $definition->addArgument(new Reference($connection));
            $container->setDefinition(
                sprintf('tusk_redis_mq.connection.%s', $name),
                $definition
            );
            $definition = new Definition($monitorClass);
            $definition->addArgument(
                new Reference(sprintf('tusk_redis_mq.connection.%s', $name))
            );
            $container->setDefinition(
                sprintf('tusk_redis_mq.monitor.%s', $name),
                $definition
            );
        }
    }

    private function loadProducers(array $producers, ContainerBuilder $container)
    {
        $class = $container->getParameter('tusk_redis_mq.producer.class');
        foreach ($producers as $name => $producer) {
            $definition = new Definition($class);
            $definition->addArgument($producer['channel']);
            $definition->addArgument(
                new Reference(
                    sprintf(
                        'tusk_redis_mq.connection.%s',
                        $producer['connection']
                    )
                )
            );
            $container->setDefinition(
                sprintf('tusk_redis_mq.producer.%s', $name),
                $definition
            );
        }
    }

    private function loadConsumers(array $consumers, ContainerBuilder $container)
    {
        $class = $container->getParameter('tusk_redis_mq.consumer.class');
        foreach ($consumers as $name => $consumer) {
            $definition = new Definition($class);
            $definition->addArgument($consumer['channel']);
            if (count($consumer['callback']) == 1) {
                $definition->addArgument(new Reference($consumer['callback'][0]));
            } elseif (count($consumer['callback']) == 2) {
                $definition->addArgument(
                    array(
                        new Reference($consumer['callback'][0]),
                        $consumer['callback'][1]
                    )
                );
            } else {
                throw new \UnexpectedValueException(
                    sprintf(
                        'Invalid callback configuration provided for the service "%s".',
                        sprintf('tusk_redis_mq.consumer.%s', $name)
                    )
                );
            }
            $definition->addArgument(
                new Reference(
                    sprintf(
                        'tusk_redis_mq.connection.%s',
                        $consumer['connection']
                    )
                )
            );
            $container->setDefinition(
                sprintf('tusk_redis_mq.consumer.%s', $name),
                $definition
            );
        }
    }

    private function loadRpc(array $rpcs, ContainerBuilder $container)
    {
        $class = $container->getParameter('tusk_redis_mq.rpc_factory.class');
        foreach ($rpcs as $name => $rpc) {
            $definition = new Definition($class);
            $definition->addArgument(
                new Reference(
                    sprintf(
                        'tusk_redis_mq.connection.%s',
                        $rpc['connection']
                    )
                )
            );
            $options = array();
            if (isset($rpc['listen_timeout'])) {
                $options['listenTimeout'] = $rpc['listen_timeout'];
            }
            if (isset($rpc['response_timeout'])) {
                $options['responseTimeout'] = $rpc['response_timeout'];
            }
            if (isset($rpc['channel_expire'])) {
                $options['channelExpire'] = $rpc['channel_expire'];
            }
            if (count($options) > 0) {
                $definition->addArgument($options);
            }
            $container->setDefinition(
                sprintf('tusk_redis_mq.rpc.%s', $name),
                $definition
            );
        }
    }
}
