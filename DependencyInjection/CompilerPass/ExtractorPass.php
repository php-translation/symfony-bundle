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
use Translation\Extractor\Extractor;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ExtractorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $extractors = $this->getExtractors($container);
        $this->addVisitors($container, $extractors);
    }

    /**
     * @return array type => Definition[]
     */
    private function getExtractors(ContainerBuilder $container): array
    {
        if (!$container->hasDefinition(Extractor::class)) {
            return [];
        }

        /** @var Definition $def */
        $def = $container->getDefinition(Extractor::class);
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

    private function addVisitors(ContainerBuilder $container, array $extractors): void
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

                /** @var Definition $extractor */
                foreach ($extractors[$tag['type']] as $extractor) {
                    $extractor->addMethodCall('addVisitor', [new Reference($id)]);
                }
            }
        }
    }
}
