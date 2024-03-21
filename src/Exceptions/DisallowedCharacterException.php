<?php

namespace VanOns\LaravelAttachmentLibrary\Exceptions;

use Throwable;

class DisallowedCharacterException extends \Exception
{
    public function __construct(string $message = 'The given file/directory name contains disallowed characters.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
