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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Translation\Bundle\EditInPlace\ActivatorInterface;

/**
 * Custom Translator for HTML rendering only (output `<x-trans>` tags).
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
final class EditInPlaceTranslator implements TranslatorInterface, TranslatorBagInterface
{
    /**
     * @var TranslatorInterface|\Symfony\Component\Translation\Translator
     */
    private $translator;

    /**
     * @var ActivatorInterface
     */
    private $activator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(TranslatorInterface $translator, ActivatorInterface $activator, RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->activator = $activator;
        $this->requestStack = $requestStack;
    }

    /**
     * @see Translator::getCatalogue
     */
    public function getCatalogue($locale = null)
    {
        return $this->translator->getCatalogue($locale);
    }

    /**
     * @see Translator::trans
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $original = $this->translator->trans($id, $parameters, $domain, $locale);
        if (!$this->activator->checkRequest($this->requestStack->getMasterRequest())) {
            return $original;
        }

        $plain = $this->translator->trans($id, [], $domain, $locale);

        if (null === $domain) {
            $domain = 'messages';
        }
        if (null === $locale) {
            $locale = $this->translator->getLocale();
        }

        // Render all data in the translation tag required to allow in-line translation
        return sprintf('<x-trans data-key="%s|%s" data-value="%s" data-plain="%s" data-domain="%s" data-locale="%s">%s</x-trans>',
            $domain,
            $id,
            $original,
            $plain,
            $domain,
            $locale,
            $original
        );
    }

    /**
     * @see Translator::trans
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        if (!$this->activator->checkRequest($this->requestStack->getMasterRequest())) {
            return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
        }

        $parameters = array_merge([
            '%count%' => $number,
        ], $parameters);

        return $this->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @see Translator::trans
     */
    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
    }

    /**
     * @see Translator::trans
     */
    public function getLocale()
    {
        return $this->translator->getLocale();
    }
}
