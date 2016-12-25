<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\EditInPlace;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\TranslatorBagInterface;

/**
 * Custom Translator for HTML rendering only (output <x-trans> tags)
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
class Translator implements TranslatorInterface, TranslatorBagInterface
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
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        // @todo cache the result of this method for performance?
        if (!$this->activator->checkRequest($this->requestStack->getMasterRequest())) {
            return $this->translator->trans($id, $parameters, $domain, $locale);
        }

        if (null === $domain) {
            $domain = 'messages';
        }

        //$original = $this->getTranslator()->getCatalogue()->get((string) $message, $domain);
        $original = $this->translator->trans($id, $parameters, $domain, $locale);

        // todo add data-value="" or data-type with real content, add parameters, domain...
        return sprintf('<x-trans data-key="%s|%s">%s</x-trans>',
            $domain,
            $id,
            $original
        );
    }

    /**
     * @see Translator::trans
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        // @todo cache the result of this method for performance?
        if (!$this->activator->checkRequest($this->requestStack->getMasterRequest())) {
            return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
        }

        $parameters = array_merge(array(
            '%count%' => $number,
        ), $parameters);

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
