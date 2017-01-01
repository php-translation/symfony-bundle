<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Catalogue;

use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Translation\Bundle\Service\ConfigurationManager;

/**
 * Write catalogues back to disk.
 *
 * This should be considered as a WriteToCache service.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CatalogueWriter
{
    /**
     * @var TranslationWriter
     */
    private $writer;

    /**
     * @var ConfigurationManager
     */
    private $configManager;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     *
     * @param TranslationWriter $writer
     * @param ConfigurationManager $configManager
     * @param string $defaultLocale
     */
    public function __construct(
        TranslationWriter $writer,
        ConfigurationManager $configManager,
        $defaultLocale
    ) {
        $this->writer = $writer;
        $this->configManager = $configManager;
        $this->defaultLocale = $defaultLocale;
    }


    /**
     * @param string $configName
     * @param MessageCatalogue[] $catalogues
     */
    public function writeCatalogues($configName, array $catalogues)
    {
        $config = $this->configManager->getConfiguration($configName);
        foreach ($catalogues as $catalogue) {
            $this->writer->writeTranslations(
                $catalogue,
                $config->getOutputFormat(),
                [
                    'path' => $config->getOutputDir(),
                    'default_locale' => $this->defaultLocale,
                ]
            );
        }
    }
}
