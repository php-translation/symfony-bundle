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

/**
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
interface ActivatorInterface
{
    /**
     * Tells if the Edit In Place mode is enabled for this request.
     */
    public function checkRequest(Request $request = null): bool;
}
