<?php

namespace VanOns\LaravelAttachmentLibrary\Exceptions;

use Exception;
use Throwable;

class IncompatibleClassMappingException extends Exception
{
    public function __construct(
        string $givenClass = 'given',
        string $requiredClass = 'required',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            sprintf('The %s class does not extend the %s class.', $givenClass, $requiredClass),
            $code,
            $previous
        );
    }
}
