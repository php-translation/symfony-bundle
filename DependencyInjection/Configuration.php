<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files.
 */
class Configuration implements ConfigurationInterface
{
    private $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('translation');

        $this->configsNode($root);
        $this->addAutoTranslateNode($root);
        $this->addEditInPlaceNode($root);
        $this->addWebUINode($root);

        $root
            ->children()
                ->arrayNode('locales')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('default_locale')->info('Your default language or fallback locale. Default will be kernel.default_locale')->end()
                ->arrayNode('symfony_profiler')
                    ->canBeEnabled()
                    ->children()
                        ->booleanNode('allow_edit')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('auto_add_missing_translations')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('config_name')->defaultValue('default')->end()
                    ->end()
                ->end()
                ->scalarNode('http_client')->cannotBeEmpty()->defaultValue('httplug.client')->end()
                ->scalarNode('message_factory')->cannotBeEmpty()->defaultValue('httplug.message_factory')->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $root
     */
    private function configsNode(ArrayNodeDefinition $root)
    {
        $container = $this->container;
        $root->children()
            ->arrayNode('configs')
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->fixXmlConfig('dir', 'dirs')
                    ->fixXmlConfig('excluded_dir')
                    ->fixXmlConfig('excluded_name')
                    ->fixXmlConfig('blacklist_domain')
                    ->fixXmlConfig('external_translations_dir')
                    ->fixXmlConfig('whitelist_domain')
                    ->children()
                        ->arrayNode('dirs')
                            ->info('Directories we should scan for translation files')
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')
                                ->validate()
                                    ->always(function ($value) use ($container) {
                                        $value = str_replace(DIRECTORY_SEPARATOR, '/', $value);

                                        if ('@' === $value[0]) {
                                            if (false === $pos = strpos($value, '/')) {
                                                $bundleName = substr($value, 1);
                                            } else {
                                                $bundleName = substr($value, 1, $pos - 2);
                                            }

                                            $bundles = $container->getParameter('kernel.bundles');
                                            if (!isset($bundles[$bundleName])) {
                                                throw new \Exception(sprintf('The bundle "%s" does not exist. Available bundles: %s', $bundleName, array_keys($bundles)));
                                            }

                                            $ref = new \ReflectionClass($bundles[$bundleName]);
                                            $value = false === $pos ? dirname($ref->getFileName()) : dirname($ref->getFileName()).substr($value, $pos);
                                        }

                                        if (!is_dir($value)) {
                                            throw new \Exception(sprintf('The directory "%s" does not exist.', $value));
                                        }

                                        return $value;
                                    })
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('excluded_dirs')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('excluded_names')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('external_translations_dirs')
                            ->prototype('scalar')->end()
                        ->end()
                        ->enumNode('output_format')->values(['php', 'yml', 'xlf'])->defaultValue('xlf')->end()
                        ->arrayNode('blacklist_domains')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('whitelist_domains')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('remote_storage')
                            ->info('Service ids with to classes that supports remote storage of translations.')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('local_storage')
                            ->info('Service ids with to classes that supports local storage of translations.')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('output_dir')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('project_root')->info("The root dir of your project. By default this will be kernel_root's parent. ")->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    private function addAutoTranslateNode(ArrayNodeDefinition $root)
    {
        $root->children()
            ->arrayNode('fallback_translation')
                ->canBeEnabled()
                ->children()
                    ->enumNode('service')->values(['google', 'bing'])->defaultValue('google')->end()
                    ->scalarNode('api_key')->defaultNull()->end()
                ->end()
            ->end()
        ->end();
    }

    private function addEditInPlaceNode(ArrayNodeDefinition $root)
    {
        $root->children()
            ->arrayNode('edit_in_place')
                ->canBeEnabled()
                ->children()
                    ->scalarNode('config_name')->defaultValue('default')->end()
                    ->scalarNode('activator')->cannotBeEmpty()->defaultValue('php_translation.edit_in_place.activator')->end()
                ->end()
            ->end()
        ->end();
    }
    private function addWebUINode(ArrayNodeDefinition $root)
    {
        $root->children()
            ->arrayNode('webui')
                ->canBeEnabled()
                ->children()
                    ->booleanNode('allow_create')->defaultTrue()->end()
                    ->booleanNode('allow_delete')->defaultTrue()->end()
                ->end()
            ->end()
        ->end();
    }
}
