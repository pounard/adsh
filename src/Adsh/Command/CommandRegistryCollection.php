<?php

namespace Adsh\Command;

use Adsh\Configuration\AbstractRegistryCollection;

class CommandRegistryCollection extends AbstractRegistryCollection implements CommandRegistryInterface
{
    public function __construct()
    {
        parent::__construct('\Adsh\Command\CommandRegistryInterface');
    }
}
