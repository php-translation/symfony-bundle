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
     * Usually dirname('kernel.project_dir').
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    public function getProjectRoot(): string
    {
        return $this->projectRoot;
    }

    public function getOutputDir(): string
    {
        return $this->outputDir;
    }

    public function getDirs(): array
    {
        return $this->dirs;
    }

    public function getExcludedDirs(): array
    {
        return $this->excludedDirs;
    }

    public function getExcludedNames(): array
    {
        return $this->excludedNames;
    }

    public function getExternalTranslationsDirs(): array
    {
        return $this->externalTranslationsDirs;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    public function getBlacklistDomains(): array
    {
        return $this->blacklistDomains;
    }

    public function getWhitelistDomains(): array
    {
        return $this->whitelistDomains;
    }

    /**
     * Get all paths where translation paths are stored. Both external files and
     * files created by us.
     */
    public function getPathsToTranslationFiles(): array
    {
        return \array_merge($this->externalTranslationsDirs, [$this->getOutputDir()]);
    }

    public function getXliffVersion(): string
    {
        return $this->xliffVersion;
    }

    /**
     * Reconfigures the directories so we can use one configuration for extracting
     * the messages only from one bundle.
     */
    public function reconfigureBundleDirs(string $bundleDir, string $outputDir): void
    {
        $this->dirs = [$bundleDir];
        $this->outputDir = $outputDir;
    }
}
