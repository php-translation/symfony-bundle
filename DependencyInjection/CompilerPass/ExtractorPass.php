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
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ExtractorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $extractors = $this->getExtractors($container);
        $this->addVisitors($container, $extractors);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array type => Definition[]
     */
    private function getExtractors(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('php_translation.extractor')) {
            return [];
        }

        /** @var Definition $def */
        $def = $container->getDefinition('php_translation.extractor');
        $services = $container->findTaggedServiceIds('php_translation.extractor');
        $extractors = [];
        foreach ($services as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new \LogicException('The tag "php_translation.extractor" must have a "type".');
                }

                $extractors[$tag['type']][] = $container->getDefinition($id);
                $def->addMethodCall('addFileExtractor', [new Reference($id)]);
            }
        }

        return $extractors;
    }

    /**
     * @param ContainerBuilder $container
     * @param $extractors
     */
    private function addVisitors(ContainerBuilder $container, $extractors)
    {
        $services = $container->findTaggedServiceIds('php_translation.visitor');
        foreach ($services as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new \LogicException('The tag "php_translation.visitor" must have a "type".');
                }
                if (!isset($extractors[$tag['type']])) {
                    continue;
                }

                foreach ($extractors[$tag['type']] as $extractor) {
                    $extractor->addMethodCall('addVisitor', [new Reference($id)]);
                }
            }
        }
    }
}
