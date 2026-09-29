<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Translator;

use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface as NewTranslatorInterface;

/**
 * Translator interface combining the new TranslatorInterface with TranslatorBagInterface.
 */
interface TranslatorInterface extends NewTranslatorInterface, LocaleAwareInterface, TranslatorBagInterface
{
}
