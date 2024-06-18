<?php

namespace VanOns\LaravelAttachmentLibrary\Exceptions;

class NoParentDirectoryException extends \Exception
{
    public function __construct(string $message = 'There is no existing path to given destination.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
