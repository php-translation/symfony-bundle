<?php

namespace Translation\Bundle\Exception;

use Translation\Common\Exception;

class MessageValidationException extends \Exception implements Exception
{
    public static function create($message = 'Validation of the translation message failed.')
    {
        return new self($message);
    }
}
