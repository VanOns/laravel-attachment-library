<?php

namespace VanOns\LaravelAttachmentLibrary\Exceptions;

use Throwable;

class NoParentDirectoryException extends \Exception
{
    public function __construct(string $message = 'There is no existing path to given destination.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
