<?php

namespace VanOns\LaravelAttachmentLibrary\Exceptions;

use Throwable;

class IncompatibleModelConfiguration extends \Exception
{
    public function __construct(string $message = 'The configured model does not extend the Attachment class.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
