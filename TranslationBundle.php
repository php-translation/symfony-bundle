<?php

namespace Translation\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Translation\Bundle\DependencyInjection\CompilerPass\ExtractorPass;

class TranslationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExtractorPass());
    }
}
