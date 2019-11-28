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

/**
 * If the "validation" service does not exists, then disable the visitor.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ValidatorVisitorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('validator')) {
            return;
        }

        $container->removeDefinition('php_translation.extractor.php.visitor.ValidationAnnotation');
    }
}
