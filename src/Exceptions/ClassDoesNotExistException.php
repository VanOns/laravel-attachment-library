<?php

namespace VanOns\LaravelAttachmentLibrary\Exceptions;

class ClassDoesNotExistException extends \Exception
{
    public function __construct(string $class, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            "The given {$class} class does not exist.",
            $code,
            $previous
        );
    }
}
