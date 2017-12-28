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

/**
 * To provide a BC layer for Symfony 2.7 to 3.3 this compiler pass
 * registers an alias of whether TranslationReader or TranslationLoader
 * to be able to inject it in other services.
 */
class LoaderOrReaderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('translation.reader')) {
            $container->setAlias('translation.loader_or_reader', 'translation.reader');

            return;
        }

        if ($container->has('translation.loader')) {
            $container->setAlias('translation.loader_or_reader', 'translation.loader');

            return;
        }
    }
}
