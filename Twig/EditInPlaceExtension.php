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

/**
 * Override the `trans` functions `is_safe` option to allow HTML output from the
 * translator. This extension is used by for the EditInPlace feature.
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
final class EditInPlaceExtension extends \Symfony\Bridge\Twig\Extension\TranslationExtension
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
            new \Twig_SimpleFilter('trans', [$this, 'trans'], ['is_safe_callback' => [$this, 'isSafe']]),
            new \Twig_SimpleFilter('transchoice', [$this, 'transchoice'], ['is_safe_callback' => [$this, 'isSafe']]),
        ];
    }

    /**
     * Escape output if the EditInPlace is disabled.
     *
     * @return array
     */
    public function isSafe($node)
    {
        return $this->activator->checkRequest($this->requestStack->getMasterRequest()) ? ['html'] : [];
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
