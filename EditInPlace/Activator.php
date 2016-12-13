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
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
class Activator
{
    const TOKEN_KEY = 'translation_bundle.edit_in_place.token';

    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function generateUniqueToken()
    {
        $random = preg_replace('/[^a-zA-Z0-9]+/', '', base64_encode(random_bytes(10)));

        $this->session->set(self::TOKEN_KEY, $random);

        return $random;
    }

    public function checkRequest(Request $request = null)
    {
        return true;

        if (!$request) {
            return false;
        }

        if (!$this->session->has(self::TOKEN_KEY)) {
            return false;
        }

        if (empty($request->get('translation_token'))) {
            return false;
        }

        return hash_equals($this->session->get(self::TOKEN_KEY), $request->get('translation_token'));
    }
}
