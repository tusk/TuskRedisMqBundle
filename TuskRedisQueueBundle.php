<?php

/*
 * This file is part of the Tusk RedisQueueBundle package.
 *
 * (c) 2012 Tusk PHP Components <frizzy@paperjaw.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tusk\RedisQueueBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tusk\RedisQueueBundle\DependencyInjection\Compiler\ConsumerCompilerPass;

/**
 * TuskRedisQueueBundle
 *
 * @author Bernard van Niekerk <frizzy@paperjaw.com>
 */
class TuskRedisQueueBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ConsumerCompilerPass());
    }
}
