<?php

namespace Translation\Bundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
