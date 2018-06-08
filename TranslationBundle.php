<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Translation\Bundle\DependencyInjection\CompilerPass\EditInPlacePass;
use Translation\Bundle\DependencyInjection\CompilerPass\ExternalTranslatorPass;
use Translation\Bundle\DependencyInjection\CompilerPass\ExtractorPass;
use Translation\Bundle\DependencyInjection\CompilerPass\FileDumperBackupPass;
use Translation\Bundle\DependencyInjection\CompilerPass\LoaderOrReaderPass;
use Translation\Bundle\DependencyInjection\CompilerPass\StoragePass;
use Translation\Bundle\DependencyInjection\CompilerPass\SymfonyProfilerPass;
use Translation\Bundle\DependencyInjection\CompilerPass\ValidatorVisitorPass;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class TranslationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SymfonyProfilerPass());
        $container->addCompilerPass(new ValidatorVisitorPass());
        $container->addCompilerPass(new ExternalTranslatorPass());
        $container->addCompilerPass(new ExtractorPass());
        $container->addCompilerPass(new StoragePass());
        $container->addCompilerPass(new EditInPlacePass());
        $container->addCompilerPass(new LoaderOrReaderPass());
        $container->addCompilerPass(new FileDumperBackupPass());
    }
}
