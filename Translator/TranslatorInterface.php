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
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface as NewTranslatorInterface;

/*
 * This interface is here to allow us to support both sf 3.x with
 * LegacyTranslatorInterface & sf 5.x where this interface have been replaced
 * by NewLocalAwareInterface.
 *
 * When sf 3.4 won't be supported anymore, this interface will become useless.
 */

if (\interface_exists(NewTranslatorInterface::class)) {
    interface TranslatorInterface extends NewTranslatorInterface, LocaleAwareInterface, TranslatorBagInterface
    {
    }
} else {
    interface TranslatorInterface extends LegacyTranslatorInterface, TranslatorBagInterface
    {
    }
}
