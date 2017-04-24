<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Make sure we have all the dependencies for Symfony Profiler
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SymfonyProfilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /* @var Definition $def */
        if (!$container->hasDefinition('php_translation.data_collector')) {
            return;
        }

        if (!$container->hasDefinition('translator.data_collector')) {
            throw new \LogicException('[PHP-Translation] To integrate with the Symfony profiler you first need to enable it. Please set framework.translator.enabled: true');
        }
    }
}
