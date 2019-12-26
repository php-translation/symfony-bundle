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
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ExternalTranslatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('php_translation.translator_service.external_translator')) {
            return;
        }

        $services = $container->findTaggedServiceIds('php_translation.external_translator');
        $translators = [];
        foreach ($services as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['priority'])) {
                    $tag['priority'] = 0;
                }
                $translators[$id] = $tag['priority'];
            }
        }

        // Sort by priority
        \asort($translators);

        $def = $container->getDefinition('php_translation.translator_service.external_translator');
        foreach ($translators as $id => $prio) {
            $def->addMethodCall('addTranslatorService', [new Reference($id)]);
        }
    }
}
