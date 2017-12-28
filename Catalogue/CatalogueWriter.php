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

use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Symfony\Component\Translation\Writer\TranslationWriterInterface;
use Translation\Bundle\Model\Configuration;
use Translation\SymfonyStorage\LegacyTranslationWriter;

/**
 * Write catalogues back to disk.
 *
 * This should be considered as a "WriteToCache" service.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class CatalogueWriter
{
    /**
     * @var TranslationWriterInterface
     */
    private $writer;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @param TranslationWriter $writer
     * @param string            $defaultLocale
     */
    public function __construct(TranslationWriter $writer, $defaultLocale)
    {
        if (!$writer instanceof TranslationWriterInterface) {
            $writer = new LegacyTranslationWriter($writer);
        }

        $this->writer = $writer;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param Configuration      $config
     * @param MessageCatalogue[] $catalogues
     */
    public function writeCatalogues(Configuration $config, array $catalogues)
    {
        foreach ($catalogues as $catalogue) {
            $this->writer->write(
                $catalogue,
                $config->getOutputFormat(),
                [
                    'path' => $config->getOutputDir(),
                    'default_locale' => $this->defaultLocale,
                    'xliff_version' => $config->getXliffVersion(),
                ]
            );
        }
    }
}
