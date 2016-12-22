<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\EditInPlace\Twig;

/**
 * Just override the trans functions "is_safe" option to allow HTML output from the translator
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
class TranslationExtension extends \Symfony\Bridge\Twig\Extension\TranslationExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('trans', array($this, 'trans'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('transchoice', array($this, 'transchoice'), array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::class;
    }
}
