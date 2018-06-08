<?php

namespace Translation\Bundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * The FileDumper::setBackup is deprecated since Symfony 4.1.
 * This compiler pass assures our service definition remains unchanged for older symfony versions (3 or lower)
 * while keeping the latest version clean of deprecation notices.
 */
class FileDumperBackupPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (Kernel::MAJOR_VERSION <= 3) {
            $definition = $container->getDefinition('php_translation.storage.xlf_dumper');
            $definition->addMethodCall('setBackup', [false]);
        }
    }
}
