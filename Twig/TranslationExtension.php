<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Translation\Bundle\EditInPlace\ActivatorInterface;
use Twig\Environment;

/**
 * Override the `trans` functions `is_safe` option to allow HTML output from the
 * translator. This extension is used by for the EditInPlace feature.
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
class TranslationExtension extends \Symfony\Bridge\Twig\Extension\TranslationExtension
{
    /**
     * @var ActivatorInterface
     */
    private $activator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('trans', [$this, 'transAutoEscape'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new \Twig_SimpleFilter('transchoice', [$this, 'transchoiceAutoEscape'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    /**
     * Escape output if the EditInPlace is disabled.
     *
     * @return bool
     */
    private function escapeOutput()
    {
        return !$this->activator->checkRequest($this->requestStack->getMasterRequest());
    }

    public function transAutoEscape(Environment $env, $message, array $arguments = [], $domain = null, $locale = null)
    {
        $value = $this->trans($message, $arguments, $domain, $locale);

        if ($this->escapeOutput()) {
            return  twig_escape_filter($env, $value, 'html', null, true);
        }

        return $value;
    }

    public function transchoiceAutoEscape(Environment $env, $message, $count, array $arguments = [], $domain = null, $locale = null)
    {
        $value = $this->transchoice($message, $count, $arguments, $domain, $locale);

        if ($this->escapeOutput()) {
            return  twig_escape_filter($env, $value, 'html', null, true);
        }

        return $value;
    }

    /**
     * @param ActivatorInterface $activator
     */
    public function setActivator(ActivatorInterface $activator)
    {
        $this->activator = $activator;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::class;
    }
}
