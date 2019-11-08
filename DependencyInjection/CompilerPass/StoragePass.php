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
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register all storages in the StorageService.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class StoragePass implements CompilerPassInterface
{
    /**
     * @var Definition[]
     */
    private $definitions;

    public function process(ContainerBuilder $container): void
    {
        $services = $container->findTaggedServiceIds('php_translation.storage');
        foreach ($services as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['name'])) {
                    $tag['name'] = 'default';
                }
                if (!isset($tag['type'])) {
                    throw new \LogicException('The tag "php_translation.storage" must have a "type".');
                }

                $def = $this->getDefinition($container, $tag['name']);
                switch ($tag['type']) {
                    case 'remote':
                        $def->addMethodCall('addRemoteStorage', [new Reference($id)]);

                        break;
                    case 'local':
                        $def->addMethodCall('addLocalStorage', [new Reference($id)]);

                        break;
                    default:
                        throw new \LogicException(\sprintf('The tag "php_translation.storage" must have a "type" of value "local" or "remote". Value "%s" was provided', $tag['type']));
                }
            }
        }
    }

    private function getDefinition(ContainerBuilder $container, string $name): Definition
    {
        if (!isset($this->definitions[$name])) {
            $this->definitions[$name] = $container->getDefinition('php_translation.storage.'.$name);
        }

        return $this->definitions[$name];
    }
}
