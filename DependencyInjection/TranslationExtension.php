<?php

namespace Translation\Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

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

        foreach ($config['configs'] as &$c) {
            if (empty($c['project_root'])) {
                $c['project_root'] = dirname($container->getParameter('kernel.root_dir'));
            }
        }

        $container->getDefinition('php_translation.configuration_manager')
            ->replaceArgument(0, $config['configs']);
    }

    private function enableWebUi(ContainerBuilder $container, $config)
    {

    }
    private function configureExtractors(ContainerBuilder $container, $config)
    {
        $def = $container->getDefinition('php_translation.extractor');

    }

    public function getAlias()
    {
        return 'translation';
    }
}
