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
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Default Activator implementation.
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
final class Activator implements ActivatorInterface
{
    const KEY = 'translation_bundle.edit_in_place.enabled';

    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Enable the Edit In Place mode.
     */
    public function activate(): void
    {
        $this->session->set(self::KEY, true);
    }

    /**
     * Disable the Edit In Place mode.
     */
    public function deactivate(): void
    {
        $this->session->remove(self::KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function checkRequest(Request $request = null): bool
    {
        if (!$this->session->has(self::KEY)) {
            return false;
        }

        return $this->session->get(self::KEY, false);
    }
}
