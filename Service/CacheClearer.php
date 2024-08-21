<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A service able to read and clear the Symfony Translation cache.
 *
 * @author Damien A. <dalexandre@jolicode.com>
 */
final class CacheClearer
{
    /**
     * @var string
     */
    private $kernelCacheDir;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(string $kernelCacheDir, $translator, Filesystem $filesystem)
    {
        // The TranslatorInterface has been deprecated in favor of Symfony\Contracts\Translation\TranslatorInterface in sf4.2.
        // Use this class to type hint event & remove the following condition once sf ^4.2 become the minimum supported version.
        // @see https://github.com/symfony/symfony/blob/master/UPGRADE-4.2.md#translation
        if (!$translator instanceof LegacyTranslatorInterface && !$translator instanceof TranslatorInterface) {
            throw new \InvalidArgumentException('Unable to deal with the given translator.');
        }

        $this->kernelCacheDir = $kernelCacheDir;
        $this->translator = $translator;
        $this->filesystem = $filesystem;
    }

    /**
     * Remove the Symfony translation cache and warm it up again.
     *
     * @param string|null $locale optional filter to clear only one locale
     */
    public function clearAndWarmUp(?string $locale = null): void
    {
        $translationDir = \sprintf('%s/translations', $this->kernelCacheDir);

        $finder = new Finder();

        // Make sure the directory exists
        $this->filesystem->mkdir($translationDir);

        // Remove the translations for this locale
        $files = $finder->files()->name($locale ? '*.'.$locale.'.*' : '*')->in($translationDir);
        foreach ($files as $file) {
            $this->filesystem->remove($file);
        }

        // Build them again
        if ($this->translator instanceof WarmableInterface) {
            $this->translator->warmUp($translationDir);
        }
    }
}
