<?php

/*
 * This file is part of the Tusk RedisQueueBundle package.
 *
 * (c) 2012 Tusk PHP Components <frizzy@paperjaw.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tusk\RedisQueueBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ConsumerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $class = $container->getParameter('tusk_redis_queue.consumer.class');
        foreach ($container->findTaggedServiceIds('tusk_redis_queue.consumer') as $id => $attributes) {
            $definition = new Definition($class);
            $definition->addArgument($attributes[0]['channel']);
            if (isset($attributes[0]['method'])) {
                $definition->addArgument(
                    array(new Reference($id), $attributes[0]['method'])
                );
            } else {
                $definition->addArgument(new Reference($id));
            }
            $definition->addArgument(
                new Reference(
                    sprintf(
                        'tusk_redis_queue.connection.%s',
                        $attributes[0]['connection']
                    )
                )
            );
            $container->setDefinition(
                sprintf('tusk_redis_queue.consumer.%s', $attributes[0]['id']),
                $definition
            );
        }
    }
}
