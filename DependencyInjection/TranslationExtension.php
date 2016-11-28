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
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->requireBundle('SimpleBusAsynchronousBundle', $container);

        // Add the command and event queue names to the consumer wrapper
        $def = $container->getDefinition('happyr.mq2php.consumer_wrapper');
        $def->replaceArgument(0, $config['command_queue'])
            ->replaceArgument(1, $config['event_queue']);

        $serializerId = 'happyr.mq2php.message_serializer';
        if (!$config['enabled']) {
            $container->removeDefinition($serializerId);

            return;
        }

        // Add default headers to the serializer
        $def = $container->getDefinition($serializerId);
        $def->replaceArgument(2, $config['message_headers']);

        // Add the secret key as parameter
        $container->setParameter('happyr.mq2php.secret_key', $config['secret_key']);
    }

    /**
     * Make sure we have activated the required bundles.
     *
     * @param $bundleName
     * @param ContainerBuilder $container
     */
    private function requireBundle($bundleName, ContainerBuilder $container)
    {
        $enabledBundles = $container->getParameter('kernel.bundles');
        if (!isset($enabledBundles[$bundleName])) {
            throw new \LogicException(sprintf('You need to enable "%s" as well', $bundleName));
        }
    }

    public function getAlias()
    {
        return 'translation';
    }


}
