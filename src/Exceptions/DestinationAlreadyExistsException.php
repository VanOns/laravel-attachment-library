<?php

namespace VanOns\LaravelAttachmentLibrary\Exceptions;

class DestinationAlreadyExistsException extends \Exception
{
    public function __construct(string $message = "The file or directory path already exists.", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}