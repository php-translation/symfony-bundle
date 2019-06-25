<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Bridge\Twig\Extension\TranslationExtension as SymfonyTranslationExtension;
use Translation\Bundle\Twig\TranslationExtension;
use Twig\Loader\ArrayLoader;

/**
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class BaseTwigTestCase extends TestCase
{
    final protected function parse($file, $debug = false)
    {
        $content = file_get_contents(__DIR__.'/Fixture/'.$file);

        $loader = class_exists(ArrayLoader::class)
            ? new ArrayLoader()
            : new \Twig_Loader_Array([]);
        $env = new \Twig_Environment($loader);
        $env->addExtension(new SymfonyTranslationExtension($translator = new IdentityTranslator(new MessageSelector())));
        $env->addExtension(new TranslationExtension($translator, $debug));

        return $env->parse($env->tokenize(new \Twig_Source($content, '')))->getNode('body');
    }
}
