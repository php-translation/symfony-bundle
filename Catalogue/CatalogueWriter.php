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
use Symfony\Component\Translation\Writer\TranslationWriterInterface;
use Translation\Bundle\Model\Configuration;

/**
 * Write catalogues back to disk.
 *
 * This should be considered as a "WriteToCache" service.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class CatalogueWriter
{
    private $writer;
    private $defaultLocale;

    public function __construct(TranslationWriterInterface $writer, string $defaultLocale)
    {
        $this->writer = $writer;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param MessageCatalogue[] $catalogues
     */
    public function writeCatalogues(Configuration $config, array $catalogues): void
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
