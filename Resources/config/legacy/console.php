<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Translation\Bundle\Command\DeleteObsoleteCommand;
use Translation\Bundle\Command\DownloadCommand;
use Translation\Bundle\Command\ExtractCommand;
use Translation\Bundle\Command\StatusCommand;
use Translation\Bundle\Command\SyncCommand;
use Translation\Bundle\Legacy\LegacyHelper;

return function (ContainerConfigurator $configurator) {
    LegacyHelper::registerDeprecatedServices($configurator->services(), [
        ['php_translator.console.delete_obsolete', DeleteObsoleteCommand::class],
        ['php_translator.console.download', DownloadCommand::class],
        ['php_translator.console.extract', ExtractCommand::class],
        ['php_translator.console.status', StatusCommand::class],
        ['php_translator.console.sync', SyncCommand::class],
    ]);
};
