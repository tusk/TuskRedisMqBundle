<?php

/*
 * This file is part of the Tusk RedisMqBundle package.
 *
 * (c) 2012 Tusk PHP Components <frizzy@paperjaw.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tusk\RedisMqBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tusk\RedisMqBundle\DependencyInjection\Compiler\ConsumerCompilerPass;

/**
 * TuskRedisMqBundle
 *
 * @author Bernard van Niekerk <frizzy@paperjaw.com>
 */
class TuskRedisMqBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ConsumerCompilerPass());
    }
}
