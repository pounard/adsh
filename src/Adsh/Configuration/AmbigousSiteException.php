<?php

namespace Adsh\Configuration;

use \RuntimeException as SplException;

class AmbigousSiteException extends UnknownSiteException
{
    public function __construct($message, $code = 0, $previous = null)
    {
        SplException::__construct($message, $code, $previous);
    }
}
