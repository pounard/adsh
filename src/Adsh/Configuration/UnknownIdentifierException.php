<?php

namespace Adsh\Configuration;

use Adsh\Exception;

use \Exception as SplException;

class UnknownIdentifierException extends SplException implements Exception
{
    public function __construct($identifier, $code = 0, $previous = null)
    {
        parent::__construct(sprintf(
            "Unknown identifier in registry: %s", $identifier), $code, $previous);
    }
}
