<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Translation\Bundle\Model\Configuration;

class ConfigurationTest extends TestCase
{
    public function testAccessors(): void
    {
        $key2Function = self::getDefaultData();
        $conf = new Configuration($key2Function);

        foreach ($key2Function as $key => $value) {
            $func = $value;
            if (\is_array($func)) {
                $func = \reset($func);
            }
            $this->assertEquals($value, $conf->$func());
        }
    }

    public function testGetPathsToTranslationFiles(): void
    {
        $data = self::getDefaultData();
        $data['external_translations_dirs'] = ['foo', 'bar'];
        $data['output_dir'] = 'biz';

        $conf = new Configuration($data);

        $this->assertEquals(['foo', 'bar', 'biz'], $conf->getPathsToTranslationFiles());
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
            'xliff_version' => 'getXliffVersion',
        ];
    }
}
