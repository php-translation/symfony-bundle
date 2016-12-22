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

/**
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
class EditInPlacePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /* @var Definition $def */
        if (!$container->hasDefinition('php_translator.edit_in_place.xtrans_html_translator')) {
            return;
        }

        // Replace the Twig Translator by a custom HTML one
        $container->getDefinition('twig.extension.trans')->replaceArgument(
            0,
            new Reference('php_translator.edit_in_place.xtrans_html_translator')
        );
    }
}
