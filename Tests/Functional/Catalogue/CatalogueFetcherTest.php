<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Translation\MessageCatalogue;
use Translation\Bundle\Catalogue\CatalogueFetcher;
use Translation\Bundle\Model\Configuration;
use Translation\Bundle\Tests\Functional\BaseTestCase;

class CatalogueFetcherTest extends BaseTestCase
{
    /**
     * @var CatalogueFetcher
     */
    private $catalogueFetcher;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \file_put_contents(
            __DIR__.'/../app/Resources/translations/messages.sv.xlf',
            <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="fr-FR" trgLang="en-US">
    <file id="messages.en_US">
        <unit id="LCa0a2j">
            <segment>
                <source>key0</source>
                <target>trans0</target>
            </segment>
        </unit>
        <unit id="LCa0a2b">
            <segment>
                <source>key1</source>
                <target>trans1</target>
            </segment>
        </unit>
    </file>
</xliff>

XML
        );
    }

    public function testFetchCatalogue(): void
    {
        $this->bootKernel();

        $this->catalogueFetcher = $this->getContainer()->get('php_translation.catalogue_fetcher');

        $data = self::getDefaultData();
        $data['external_translations_dirs'] = [__DIR__.'/../app/Resources/translations/'];

        $conf = new Configuration($data);

        /** @var MessageCatalogue[] $catalogues */
        $catalogues = $this->catalogueFetcher->getCatalogues($conf, ['sv']);

        $this->assertEquals('sv', $catalogues[0]->getLocale());
    }

    public static function getDefaultData(): array
    {
        return [
            'name' => 'getName',
            'locales' => ['getLocales'],
            'project_root' => 'getProjectRoot',
            'output_dir' => 'getOutputDir',
            'dirs' => ['getDirs'],
            'excluded_dirs' => ['getExcludedDirs'],
            'excluded_names' => ['getExcludedNames'],
            'external_translations_dirs' => ['getExternalTranslationsDirs'],
            'output_format' => 'getOutputFormat',
            'blacklist_domains' => ['getBlacklistDomains'],
            'whitelist_domains' => ['getWhitelistDomains'],
            'xliff_version' => ['getXliffVersion'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel->addConfigFile(__DIR__.'/../app/config/normal_config.yml');
    }
}
