<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Exception;

use Translation\Common\Exception;

class MessageValidationException extends \Exception implements Exception
{
    public static function create($message = 'Validation of the translation message failed.')
    {
        return new self($message);
    }
}
