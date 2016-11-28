<?php

namespace Translation\Bundle;

use Happyr\Mq2phpBundle\DependencyInjection\Compiler\RegisterConsumers;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TranslationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
    }
}
