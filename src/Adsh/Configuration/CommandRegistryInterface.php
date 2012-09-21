<?php

namespace Adsh\Configuration;

interface CommandRegistryInterface
{
    /**
     * Get command by name or alias
     *
     * @return Symfony\Component\Console\Command\Command Command instance
     * @throws \InvalidArgumentException                 If command not found
     */
    public function getInstance($name);

    /**
     * Get all instances
     *
     * Symfony by design will need all commands to be instanciated when listing
     * for help, so we need a way to give it the full list of instanciated
     * commands
     *
     * @return array Array of Symfony\Component\Console\Command\Command 
     *               instances
     */
    public function getAllInstances();
}
