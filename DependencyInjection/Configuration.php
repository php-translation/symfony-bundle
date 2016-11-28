<?php

namespace Translation\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('translation');

        $this->addExtractorNode($root);
        $this->addAutoTranslateNode($root);
        $this->addTranslationServiceNode($root);

        $root
            ->children()
                ->arrayNode('symfony_profiler')
                    ->canBeEnabled()
                    ->children()
                        ->booleanNode('allow_edit')->defaultTrue()->end()
                ->end()
                ->arrayNode('webui')
                    ->canBeEnabled()
                    ->children()
                        ->booleanNode('allow_add')->defaultTrue()->end()
                ->end()
                ->scalarNode('http_client')->cannotBeEmpty()->defaultValue('httplug.client')->end()
                ->scalarNode('message_factory')->cannotBeEmpty()->defaultValue('httplug.message_factory')->end()
            ->end();

        return $treeBuilder;
    }

    private function addExtractorNode(ArrayNodeDefinition $root)
    {

    }

    private function addAutoTranslateNode(ArrayNodeDefinition $root)
    {

    }

    private function addTranslationServiceNode(ArrayNodeDefinition $root)
    {
        $root
            ->children()
                ->enumNode('storage')
                    ->info('Where translations are stored.')
                    ->values(array('blackhole', 'filesystem', 'loco'))
                    ->defaultValue('filesystem')
                ->end()
            ->end();
    }
}
