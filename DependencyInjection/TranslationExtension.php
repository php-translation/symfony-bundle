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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Translation\Bundle\Service\StorageService;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TranslationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container);
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yml');
        $loader->load('extractors.yml');

        $container->setParameter('php_translation.locales', $config['locales']);
        $container->setParameter('php_translation.default_locale', isset($config['default_locale']) ? $config['default_locale'] : $container->getParameter('kernel.default_locale'));

        if ($config['webui']['enabled']) {
            $this->enableWebUi($container, $config);
        }

        if ($config['symfony_profiler']['enabled']) {
            $loader->load('symfony_profiler.yml');
            $this->enableSymfonyProfiler($container, $config);
        }

        if ($config['fallback_translation']['enabled']) {
            $loader->load('auto_translation.yml');
            $this->enableFallbackAutoTranslator($container, $config);
        }

        $first = null;
        foreach ($config['configs'] as $name => &$c) {
            if ($first === null || $name === 'default') {
                $first = $name;
            }
            if (empty($c['project_root'])) {
                $c['project_root'] = dirname($container->getParameter('kernel.root_dir'));
            }

            $container->register('php_translation.storage.'.$name, StorageService::class);

            // Register a file storage
            $def = new DefinitionDecorator('php_translation.single_storage.file.abstract');
            $def->replaceArgument(2, $c['output_dir'])
                ->addTag('php_translation.storage', ['type'=>'local', 'name'=>$name]);
            $container->setDefinition('php_translation.single_storage.file.'.$name, $def);
        }

        if ($first !== null) {
            // Create some aliases for the default storage
            $container->setAlias('php_translation.storage', 'php_translation.storage.'.$first);
            $container->setAlias('php_translation.storage.default', 'php_translation.storage.'.$first);
        }

        $container->getDefinition('php_translation.configuration_manager')
            ->replaceArgument(0, $config['configs']);
    }

    private function enableWebUi(ContainerBuilder $container, $config)
    {
    }

    private function enableSymfonyProfiler(ContainerBuilder $container, $config)
    {
        $container->setParameter('php_translation.toolbar.allow_edit', $config['symfony_profiler']['allow_edit']);
    }

    private function enableFallbackAutoTranslator(ContainerBuilder $container, $config)
    {
        $externalTranslatorId = 'php_translation.translator_service.'.$config['fallback_translation']['service'];
        $externalTranslatorDef = $container->getDefinition($externalTranslatorId);
        $externalTranslatorDef->addTag('php_translation.external_translator');
        $externalTranslatorDef->addArgument(new Reference($config['http_client']));
        $externalTranslatorDef->addArgument(new Reference($config['message_factory']));

        $container->setParameter('php_translation.translator_service.api_key', $config['fallback_translation']['api_key']);
    }

    public function getAlias()
    {
        return 'translation';
    }
}
