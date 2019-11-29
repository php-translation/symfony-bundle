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

use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Translation\Bundle\EditInPlace\ActivatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Override the `trans` functions `is_safe` option to allow HTML output from the
 * translator. This extension is used by for the EditInPlace feature.
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
final class EditInPlaceExtension extends AbstractExtension
{
    private $extension;
    private $requestStack;
    private $activator;

    public function __construct(TranslationExtension $extension, RequestStack $requestStack, ActivatorInterface $activator)
    {
        $this->extension = $extension;
        $this->requestStack = $requestStack;
        $this->activator = $activator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('trans', [$this->extension, 'trans'], ['is_safe_callback' => [$this, 'isSafe']]),
            new TwigFilter('transchoice', [$this->extension, 'transchoice'], ['is_safe_callback' => [$this, 'isSafe']]),
        ];
    }

    /**
     * Escape output if the EditInPlace is disabled.
     */
    public function isSafe($node): array
    {
        return $this->activator->checkRequest($this->requestStack->getMasterRequest()) ? ['html'] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::class;
    }
}
