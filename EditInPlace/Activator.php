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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Default Activator implementation.
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
final class Activator implements ActivatorInterface
{
    const KEY = 'translation_bundle.edit_in_place.enabled';

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Enable the Edit In Place mode.
     */
    public function activate(): void
    {
        $this->requestStack->getSession()->set(self::KEY, true);
    }

    /**
     * Disable the Edit In Place mode.
     */
    public function deactivate(): void
    {
        $this->requestStack->getSession()->remove(self::KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function checkRequest(Request $request = null): bool
    {
        if (!$this->requestStack->getSession()->has(self::KEY)) {
            return false;
        }

        return $this->requestStack->getSession()->get(self::KEY, false);
    }
}
