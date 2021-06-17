<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Legacy;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * A legacy helper to suppress deprecations on RequestStack.
 *
 * @author Victor Bocharsky <bocharsky.bw@gmail.com>
 */
class LegacyHelper
{
    public static function getMainRequest(RequestStack $requestStack)
    {
        if (\method_exists($requestStack, 'getMainRequest')) {
            return $requestStack->getMainRequest();
        }

        return $requestStack->getMasterRequest();
    }
}
