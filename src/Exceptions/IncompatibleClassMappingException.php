<?php

namespace VanOns\LaravelAttachmentLibrary\Exceptions;

class IncompatibleClassMappingException extends \Exception
{
    public function __construct(
        string $givenClass = 'given',
        string $requiredClass = 'required',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            "The {$givenClass} class does not extend the {$requiredClass} class.",
            $code,
            $previous
        );
    }
}
