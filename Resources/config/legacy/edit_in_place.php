<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Translation\Bundle\EditInPlace\Activator;
use Translation\Bundle\EventListener\EditInPlaceResponseListener;
use Translation\Bundle\Legacy\LegacyHelper;
use Translation\Bundle\Translator\EditInPlaceTranslator;
use Translation\Bundle\Twig\EditInPlaceExtension;

return function (ContainerConfigurator $configurator) {
    LegacyHelper::registerDeprecatedServices($configurator->services(), [
        ['php_translation.edit_in_place.response_listener', EditInPlaceResponseListener::class],
        ['php_translation.edit_in_place.activator', Activator::class],
        ['php_translator.edit_in_place.xtrans_html_translator', EditInPlaceTranslator::class],
        ['php_translation.edit_in_place.extension.trans', EditInPlaceExtension::class],
    ]);
};
