<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\EditInPlace\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Translation\Bundle\EditInPlace\Activator;

/**
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
class TranslationExtension extends \Symfony\Bridge\Twig\Extension\TranslationExtension
{
    /**
     * @var Activator
     */
    private $activator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(TranslatorInterface $translator, Activator $activator, RequestStack $requestStack, \Twig_NodeVisitorInterface $translationNodeVisitor = null)
    {
        parent::__construct($translator, $translationNodeVisitor);
        $this->activator = $activator;
        $this->requestStack = $requestStack;
    }

    public function trans($message, array $arguments = array(), $domain = null, $locale = null)
    {
        // @todo cache the result of this method for performance?
        if (!$this->activator->checkRequest($this->requestStack->getMasterRequest())) {
            return parent::trans($message, $arguments, $domain, $locale);
        }

        if (null === $domain) {
            $domain = 'messages';
        }

        //$original = $this->getTranslator()->getCatalogue()->get((string) $message, $domain);
        $original = $this->getTranslator()->trans($message, $arguments, $domain, $locale);

        // todo add data-value="" or data-type with real content, add parameters, domain...
        return sprintf('<x-trans data-key="%s|%s">%s</x-trans>',
            $domain,
            $message,
            $original
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('trans', array($this, 'trans'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('transchoice', array($this, 'transchoice'), array('is_safe' => array('html'))),
        );
    }
}
