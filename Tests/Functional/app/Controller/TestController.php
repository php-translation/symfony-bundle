<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Tests\Functional\app\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
class TestController extends Controller
{
    public function translatedAction()
    {
        return $this->render('::translated.html.twig');
    }
}
