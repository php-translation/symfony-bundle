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

class TranslationExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('desc', [$this, 'runDescFilter']),
        ];
    }

    public function runDescFilter($translation, $description)
    {
        if (empty($translation)) {
            return $description;
        }

        return $translation;
    }

    public function getName()
    {
        return 'php-translation';
    }
}
