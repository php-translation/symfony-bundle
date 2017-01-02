<?php

namespace Translation\Bundle\Tests\Unit\Model;

use Translation\Bundle\Model\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $key2Function = $this->getDefaultData();

        $data = [];
        foreach ($key2Function as $key => $func) {
            $data[$key] = $key;
        }
        $conf = new Configuration($data);

        foreach ($key2Function as $key => $func) {
            $this->assertEquals($key, call_user_func([$conf, $func]));
        }
    }

    public function testGetPathsToTranslationFiles()
    {
        $data = $this->getDefaultData();
        $data['external_translations_dirs'] = ['foo', 'bar'];
        $data['output_dir'] = 'biz';

        $conf = new Configuration($data);

        $this->assertEquals(['foo', 'bar', 'biz'], $conf->getPathsToTranslationFiles());
    }

    /**
     *
     * @return array
     */
    private function getDefaultData()
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
        ];
    }
}
