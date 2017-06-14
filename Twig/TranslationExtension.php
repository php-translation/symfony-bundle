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

/**
 * Override the `trans` functions `is_safe` option to allow HTML output from the
 * translator. This extension is used by for the EditInPlace feature.
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
        return [
            new \Twig_SimpleFilter('trans', [$this, 'trans']),
            new \Twig_SimpleFilter('transchoice', [$this, 'transchoice']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::class;
    }
}
