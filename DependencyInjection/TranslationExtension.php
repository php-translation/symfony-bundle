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
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\Kernel;
use Translation\Bundle\Model\Configuration as ConfigurationModel;

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

        // Add major version to extractor
        $container->getDefinition('php_translation.extractor.php.visitor.FormTypeChoices')
            ->addMethodCall('setSymfonyMajorVersion', [Kernel::MAJOR_VERSION]);

        $container->setParameter('php_translation.locales', $config['locales']);
        $container->setParameter('php_translation.default_locale', isset($config['default_locale']) ? $config['default_locale'] : $container->getParameter('kernel.default_locale'));
        $this->handleConfigNode($container, $config);

        if ($config['webui']['enabled']) {
            $this->enableWebUi($container, $config);
        } else {
            $container->setParameter('php_translation.webui.enabled', false);
        }

        if ($config['symfony_profiler']['enabled']) {
            $loader->load('symfony_profiler.yml');
            $this->enableSymfonyProfiler($container, $config);
        }

        if ($config['edit_in_place']['enabled']) {
            $loader->load('edit_in_place.yml');
            $this->enableEditInPlace($container, $config);
        }

        if ($config['auto_add_missing_translations']['enabled']) {
            $loader->load('auto_add.yml');
            $container->getDefinition('php_translator.auto_adder')
                ->replaceArgument(0, new Reference('php_translation.storage.'.$config['auto_add_missing_translations']['config_name']));
        }

        if ($config['fallback_translation']['enabled']) {
            $loader->load('auto_translation.yml');
            $this->enableFallbackAutoTranslator($container, $config);
        }
    }

    /**
     * Handle the config node to prepare the config manager.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function handleConfigNode(ContainerBuilder $container, array $config)
    {
        $configurationManager = $container->getDefinition('php_translation.configuration_manager');
        // $first will be the "default" configuration.
        $first = null;
        foreach ($config['configs'] as $name => &$c) {
            if ($first === null || $name === 'default') {
                $first = $name;
            }
            if (empty($c['project_root'])) {
                // Add a project root of none is set.
                $c['project_root'] = dirname($container->getParameter('kernel.root_dir'));
            }
            $c['name'] = $name;
            $c['locales'] = $config['locales'];
            $configurationServiceId = 'php_translation.configuration.'.$name;
            $configDef = $container->register($configurationServiceId, ConfigurationModel::class);
            $configDef->setPublic(false)->addArgument($c);
            $configurationManager->addMethodCall('addConfiguration', [$name, new Reference($configurationServiceId)]);

            /*
             * Configure storage chain service
             */
            $storageDefinition = new ChildDefinition('php_translation.storage.abstract');
            $storageDefinition->replaceArgument(2, new Reference($configurationServiceId));
            $storageDefinition->setPublic(true);
            $container->setDefinition('php_translation.storage.'.$name, $storageDefinition);

            // Add storages
            foreach ($c['remote_storage'] as $serviceId) {
                $storageDefinition->addMethodCall('addRemoteStorage', [new Reference($serviceId)]);
            }

            foreach ($c['local_storage'] as $serviceId) {
                if ($serviceId !== 'php_translation.local_file_storage.abstract') {
                    $storageDefinition->addMethodCall('addLocalStorage', [new Reference($serviceId)]);

                    continue;
                }

                $def = new ChildDefinition($serviceId);
                $def->replaceArgument(2, [$c['output_dir']])
                    ->replaceArgument(3, [$c['local_file_storage_options']])
                    ->addTag('php_translation.storage', ['type' => 'local', 'name' => $name]);
                $container->setDefinition('php_translation.single_storage.file.'.$name, $def);
            }
        }

        if ($first !== null) {
            // Create some aliases for the default storage
            $container->setAlias('php_translation.storage', 'php_translation.storage.'.$first);
            if ($first !== 'default') {
                $container->setAlias('php_translation.storage.default', 'php_translation.storage.'.$first);
            }
        }
    }

    /**
     * Handle config for WebUI.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function enableWebUi(ContainerBuilder $container, array $config)
    {
        $container->setParameter('php_translation.webui.enabled', true);
        $container->setParameter('php_translation.webui.allow_create', $config['webui']['allow_create']);
        $container->setParameter('php_translation.webui.allow_delete', $config['webui']['allow_delete']);

        $path = $config['webui']['file_base_path'];
        if (null === $path) {
            if ($container->hasParameter('kernel.project_dir')) {
                $path = $container->getParameter('kernel.project_dir');
            } else {
                $path = $container->getParameter('kernel.root_dir').'/..';
            }
        }

        $container->setParameter('php_translation.webui.file_base_path', rtrim($path, '/').'/');
    }

    /**
     * Handle config for EditInPlace.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function enableEditInPlace(ContainerBuilder $container, array $config)
    {
        $name = $config['edit_in_place']['config_name'];

        if ($name !== 'default' && !isset($config['configs'][$name])) {
            throw new InvalidArgumentException(sprintf('There is no config named "%s".', $name));
        }

        $activatorRef = new Reference($config['edit_in_place']['activator']);

        $def = $container->getDefinition('php_translation.edit_in_place.response_listener');
        $def->replaceArgument(0, $activatorRef);
        $def->replaceArgument(3, $name);
        $def->replaceArgument(4, $config['edit_in_place']['show_untranslatable']);

        $def = $container->getDefinition('php_translator.edit_in_place.xtrans_html_translator');
        $def->replaceArgument(1, $activatorRef);
    }

    /**
     * Handle config for Symfony Profiler.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function enableSymfonyProfiler(ContainerBuilder $container, array $config)
    {
        $container->setParameter('php_translation.toolbar.allow_edit', $config['symfony_profiler']['allow_edit']);
    }

    /**
     * Handle config for fallback auto translate.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function enableFallbackAutoTranslator(ContainerBuilder $container, array $config)
    {
        $externalTranslatorId = 'php_translation.translator_service.'.$config['fallback_translation']['service'];
        $externalTranslatorDef = $container->getDefinition($externalTranslatorId);
        $externalTranslatorDef->addTag('php_translation.external_translator');
        $externalTranslatorDef->addArgument(new Reference($config['http_client']));
        $externalTranslatorDef->addArgument(new Reference($config['message_factory']));

        $container->setParameter('php_translation.translator_service.api_key', $config['fallback_translation']['api_key']);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'translation';
    }
}
