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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Kernel;
use Translation\Bundle\EventListener\AutoAddMissingTranslations;
use Translation\Bundle\EventListener\EditInPlaceResponseListener;
use Translation\Bundle\Model\Configuration as ConfigurationModel;
use Translation\Bundle\Service\ConfigurationManager;
use Translation\Bundle\Service\StorageManager;
use Translation\Bundle\Service\StorageService;
use Translation\Bundle\Translator\EditInPlaceTranslator;
use Translation\Bundle\Twig\EditInPlaceExtension;
use Translation\Extractor\Visitor\Php\Symfony\FormTypeChoices;

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
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->setParameter('extractor_vendor_dir', $this->getExtractorVendorDirectory());

        $configuration = new Configuration($container);
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yaml');
        $loader->load('extractors.yaml');

        // Add major version to extractor
        $container->getDefinition(FormTypeChoices::class)
            ->addMethodCall('setSymfonyMajorVersion', [Kernel::MAJOR_VERSION]);

        $container->setParameter('php_translation.locales', $config['locales']);
        $container->setParameter('php_translation.default_locale',
            $config['default_locale'] ?? $container->getParameter('kernel.default_locale'));
        $this->handleConfigNode($container, $config);

        if ($config['webui']['enabled']) {
            $loader->load('webui.yaml');
            $this->enableWebUi($container, $config);
        } else {
            $container->setParameter('php_translation.webui.enabled', false);
        }

        if ($config['symfony_profiler']['enabled']) {
            $loader->load('symfony_profiler.yaml');
            $this->enableSymfonyProfiler($container, $config);
        }

        if ($config['edit_in_place']['enabled']) {
            $loader->load('edit_in_place.yaml');
            $this->enableEditInPlace($container, $config);
        }

        if ($config['auto_add_missing_translations']['enabled']) {
            $loader->load('auto_add.yaml');
            $container->getDefinition(AutoAddMissingTranslations::class)
                ->replaceArgument(0, new Reference('php_translation.storage.'.$config['auto_add_missing_translations']['config_name']));
        }

        if ($config['fallback_translation']['enabled']) {
            $loader->load('auto_translation.yaml');
            $this->enableFallbackAutoTranslator($container, $config);
        }

        $loader->load('console.yaml');
    }

    /**
     * Handle the config node to prepare the config manager.
     */
    private function handleConfigNode(ContainerBuilder $container, array $config): void
    {
        $container->resolveEnvPlaceholders($config);
        $storageManager = $container->getDefinition(StorageManager::class);
        $configurationManager = $container->getDefinition(ConfigurationManager::class);
        // $first will be the "default" configuration.
        $first = null;
        foreach ($config['configs'] as $name => &$c) {
            if (null === $first || 'default' === $name) {
                $first = $name;
            }
            if (empty($c['project_root'])) {
                // Add a project root of none is set.
                $c['project_root'] = \dirname($container->getParameter('kernel.project_dir'));
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
            $storageDefinition->replaceArgument(1, new Reference($configurationServiceId));
            $storageDefinition->setPublic(true);
            $container->setDefinition('php_translation.storage.'.$name, $storageDefinition);
            $storageManager->addMethodCall('addStorage', [$name, new Reference('php_translation.storage.'.$name)]);

            // Add storages
            foreach ($c['remote_storage'] as $serviceId) {
                $storageDefinition->addMethodCall('addRemoteStorage', [new Reference($serviceId)]);
            }

            foreach ($c['local_storage'] as $serviceId) {
                if ('php_translation.local_file_storage.abstract' !== $serviceId) {
                    $storageDefinition->addMethodCall('addLocalStorage', [new Reference($serviceId)]);

                    continue;
                }

                $def = new ChildDefinition($serviceId);
                $def->replaceArgument(2, [$c['output_dir']])
                    ->replaceArgument(3, $c['local_file_storage_options'])
                    ->addTag('php_translation.storage', ['type' => 'local', 'name' => $name]);
                $container->setDefinition('php_translation.single_storage.file.'.$name, $def);
            }
        }

        if (null !== $first) {
            // Create some aliases for the default storage
            $container->setAlias('php_translation.storage', new Alias('php_translation.storage.'.$first, true));
            $container->setAlias(StorageService::class, new Alias('php_translation.storage', true));
            if ('default' !== $first) {
                $container->setAlias('php_translation.storage.default', new Alias('php_translation.storage.'.$first, true));
            }
        }
    }

    /**
     * Handle config for WebUI.
     */
    private function enableWebUi(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('php_translation.webui.enabled', true);
        $container->setParameter('php_translation.webui.allow_create', $config['webui']['allow_create']);
        $container->setParameter('php_translation.webui.allow_delete', $config['webui']['allow_delete']);

        $path = $config['webui']['file_base_path'];
        if (null === $path) {
            $path = $container->getParameter('kernel.project_dir');
        }

        $container->setParameter('php_translation.webui.file_base_path', \rtrim($path, '/').'/');
    }

    /**
     * Handle config for EditInPlace.
     */
    private function enableEditInPlace(ContainerBuilder $container, array $config): void
    {
        $name = $config['edit_in_place']['config_name'];

        if ('default' !== $name && !isset($config['configs'][$name])) {
            throw new InvalidArgumentException(\sprintf('There is no config named "%s".', $name));
        }

        $activatorRef = new Reference($config['edit_in_place']['activator']);

        $def = $container->getDefinition(EditInPlaceResponseListener::class);
        $def->replaceArgument(0, $activatorRef);
        $def->replaceArgument(3, $name);
        $def->replaceArgument(4, $config['edit_in_place']['show_untranslatable']);

        $def = $container->getDefinition(EditInPlaceTranslator::class);
        $def->replaceArgument(1, $activatorRef);

        $def = $container->getDefinition(EditInPlaceExtension::class);
        $def->replaceArgument(2, $activatorRef);
    }

    /**
     * Handle config for Symfony Profiler.
     */
    private function enableSymfonyProfiler(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('php_translation.toolbar.allow_edit', $config['symfony_profiler']['allow_edit']);
    }

    /**
     * Handle config for fallback auto translate.
     */
    private function enableFallbackAutoTranslator(ContainerBuilder $container, array $config): void
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
    public function getAlias(): string
    {
        return 'translation';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration($container);
    }

    private function getExtractorVendorDirectory(): string
    {
        $vendorReflection = new \ReflectionClass(FormTypeChoices::class);

        return \dirname($vendorReflection->getFileName(), 4);
    }
}
