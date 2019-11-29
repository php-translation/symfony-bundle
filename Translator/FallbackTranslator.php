<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Translator;

use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface as NewTranslatorInterface;
use Translation\Translator\Translator;

/**
 * This is a bridge between Symfony's translator service and Translation\Translator\Translator.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class FallbackTranslator implements TranslatorInterface
{
    /**
     * @var LegacyTranslatorInterface|NewTranslatorInterface
     */
    private $symfonyTranslator;

    /**
     * @var Translator
     */
    private $externalTranslator;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * $symfonyTranslator param can't be type hinted as we have to deal with both LegacyTranslatorInterface & NewTranslatorInterface.
     * Once we won't support sf ^3.4 anymore, we will be able to type hint $symfonyTranslator with NewTranslatorInterface.
     *
     * @param LegacyTranslatorInterface|NewTranslatorInterface $symfonyTranslator
     */
    public function __construct(string $defaultLocale, $symfonyTranslator, Translator $externalTranslator)
    {
        if (!$symfonyTranslator instanceof LegacyTranslatorInterface && !$symfonyTranslator instanceof LocaleAwareInterface) {
            throw new \InvalidArgumentException('The given translator must implements LocaleAwareInterface.');
        }

        if (!$symfonyTranslator instanceof TranslatorBagInterface) {
            throw new \InvalidArgumentException('The given translator must implements TranslatorBagInterface.');
        }

        $this->symfonyTranslator = $symfonyTranslator;
        $this->externalTranslator = $externalTranslator;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null): string
    {
        $id = (string) $id;
        if (empty($domain)) {
            $domain = 'messages';
        }

        $catalogue = $this->getCatalogue($locale);
        if ($catalogue->defines($id, $domain)) {
            return $this->symfonyTranslator->trans($id, $parameters, $domain, $locale);
        }

        $locale = $catalogue->getLocale();
        if (empty($locale) || $locale === $this->defaultLocale) {
            // we cant do anything...
            return $id;
        }

        $orgString = $this->symfonyTranslator->trans($id, $parameters, $domain, $this->defaultLocale);

        return $this->translateWithSubstitutedParameters($orgString, $locale, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null): string
    {
        $id = (string) $id;
        if (empty($domain)) {
            $domain = 'messages';
        }

        $catalogue = $this->getCatalogue($locale);
        if ($catalogue->defines($id, $domain)) {
            return $this->symfonyTranslator->transChoice($id, $number, $parameters, $domain, $locale);
        }

        $locale = $catalogue->getLocale();
        if ($locale === $this->defaultLocale) {
            // we cant do anything...
            return $id;
        }

        $orgString = $this->symfonyTranslator->transChoice($id, $number, $parameters, $domain, $this->defaultLocale);

        return $this->translateWithSubstitutedParameters($orgString, $locale, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale): void
    {
        $this->symfonyTranslator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        return $this->symfonyTranslator->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogue($locale = null): MessageCatalogueInterface
    {
        return $this->symfonyTranslator->getCatalogue($locale);
    }

    /**
     * Passes through all unknown calls onto the translator object.
     */
    public function __call(string $method, array $args)
    {
        return \call_user_func_array([$this->symfonyTranslator, $method], $args);
    }

    /**
     * @param string $orgString This is the string in the default locale. It has the values of $parameters in the string already.
     * @param string $locale    you wan to translate to
     */
    private function translateWithSubstitutedParameters(string $orgString, string $locale, array $parameters): string
    {
        // Replace parameters
        $replacements = [];
        foreach ($parameters as $placeholder => $nonTranslatableValue) {
            $replacements[(string) $nonTranslatableValue] = \sha1($placeholder);
        }

        $replacedString = \str_replace(\array_keys($replacements), \array_values($replacements), $orgString);
        $translatedString = $this->externalTranslator->translate($replacedString, $this->defaultLocale, $locale);

        if (null === $translatedString) {
            // Could not be translated
            return $orgString;
        }

        return \str_replace(\array_values($replacements), \array_keys($replacements), $translatedString);
    }
}
