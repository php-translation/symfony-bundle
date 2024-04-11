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
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Default Activator implementation.
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
final class Activator implements ActivatorInterface
{
    public const KEY = 'translation_bundle.edit_in_place.enabled';

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Session|null
     */
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Set session if available.
     */
    public function setSession(Session $session): void
    {
        $this->session = $session;
    }

    /**
     * Get session based on availability.
     */
    private function getSession(): ?Session
    {
        $session = $this->session;
        $request = $this->requestStack->getCurrentRequest();
        if (null === $session && $request && $request->hasSession()) {
            $session = $this->requestStack->getSession();
        }

        return $session;
    }

    /**
     * Enable the Edit In Place mode.
     */
    public function activate(): void
    {
        if (null !== $this->getSession()) {
            $this->getSession()->set(self::KEY, true);
        }
    }

    /**
     * Disable the Edit In Place mode.
     */
    public function deactivate(): void
    {
        if (null !== $this->getSession()) {
            $this->getSession()->remove(self::KEY);
        }
    }

    public function checkRequest(?Request $request = null): bool
    {
        if (null === $this->getSession() || !$this->getSession()->has(self::KEY)) {
            return false;
        }

        return $this->getSession()->get(self::KEY, false);
    }
}
