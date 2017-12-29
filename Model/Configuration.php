<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Model;

/**
 * This class is a PHP representation of `translation.configs.xxx`.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class Configuration
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $locales;

    /**
     * Usually dirname('kernel.root_dir').
     *
     * @var string
     */
    private $projectRoot;

    /**
     * This is where we dump all translations.
     *
     * @var string
     */
    private $outputDir;

    /**
     * The absolute path to folders we scan for translations.
     *
     * @var array
     */
    private $dirs;

    /**
     * Directories we should not extract translations from.
     *
     * @var array
     */
    private $excludedDirs;

    /**
     * File names we should not extract translations from.
     *
     * @var array
     */
    private $excludedNames;

    /**
     * Directories that holds translation files but is out of our control.
     *
     * @var array
     */
    private $externalTranslationsDirs;

    /**
     * The format we store translation files in.
     *
     * @var string
     */
    private $outputFormat;

    /**
     * @var array
     */
    private $blacklistDomains;

    /**
     * @var array
     */
    private $whitelistDomains;

    /**
     * @var string
     */
    private $xliffVersion;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->locales = $data['locales'];
        $this->projectRoot = $data['project_root'];
        $this->outputDir = $data['output_dir'];
        $this->dirs = $data['dirs'];
        $this->excludedDirs = $data['excluded_dirs'];
        $this->excludedNames = $data['excluded_names'];
        $this->externalTranslationsDirs = $data['external_translations_dirs'];
        $this->outputFormat = $data['output_format'];
        $this->blacklistDomains = $data['blacklist_domains'];
        $this->whitelistDomains = $data['whitelist_domains'];
        $this->xliffVersion = $data['xliff_version'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @return string
     */
    public function getProjectRoot()
    {
        return $this->projectRoot;
    }

    /**
     * @return string
     */
    public function getOutputDir()
    {
        return $this->outputDir;
    }

    /**
     * @return array
     */
    public function getDirs()
    {
        return $this->dirs;
    }

    /**
     * @return array
     */
    public function getExcludedDirs()
    {
        return $this->excludedDirs;
    }

    /**
     * @return array
     */
    public function getExcludedNames()
    {
        return $this->excludedNames;
    }

    /**
     * @return array
     */
    public function getExternalTranslationsDirs()
    {
        return $this->externalTranslationsDirs;
    }

    /**
     * @return string
     */
    public function getOutputFormat()
    {
        return $this->outputFormat;
    }

    /**
     * @return array
     */
    public function getBlacklistDomains()
    {
        return $this->blacklistDomains;
    }

    /**
     * @return array
     */
    public function getWhitelistDomains()
    {
        return $this->whitelistDomains;
    }

    /**
     * Get all paths where translation paths are stored. Both external files and
     * files created by us.
     *
     * @return array
     */
    public function getPathsToTranslationFiles()
    {
        return array_merge($this->externalTranslationsDirs, [$this->getOutputDir()]);
    }

    /**
     * @return string
     */
    public function getXliffVersion()
    {
        return $this->xliffVersion;
    }

    /**
     * Reconfigures the directories so we can use one configuration for extracting
     * the messages only from one bundle.
     *
     * @param string $bundleDir
     * @param string $outputDir
     */
    public function reconfigureBundleDirs($bundleDir, $outputDir)
    {
        $this->dirs = is_array($bundleDir) ? $bundleDir : [$bundleDir];
        $this->outputDir = $outputDir;
    }
}
