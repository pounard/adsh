<?php

namespace Adsh\Command;

/**
 * Array based command registry for local sites
 */
class LocalCommandRegistry implements CommandRegistryInterface
{
    /**
     * Key/value pairs of known commands
     *
     * @var array
     */
    private $commands = array();

    /**
     * Default constructor
     *
     * @param array|Traversable $commands List of SiteCommand instances
     */
    public function __construct($commands = null)
    {
        if (isset($commands)) {
            $this->addCommands($commands);
        }
    }

    /**
     * Set commands
     *
     * @param array|Traversable $commands List of SiteCommand instances
     */
    public function addCommands($commands)
    {
        if (!is_array($commands) && !$commands instanceof Traversable) {
            throw new \InvalidArgumentException(
                "Given parameter is not traversable");
        }

        foreach ($commands as $command) {
            $this->addCommand($command);
        }
    }

    /**
     * Add a single command into this registry
     *
     * @param SiteCommand $command
     */
    public function addCommand(SiteCommand $command)
    {
        $this->commands[$command->getName()] = $command;
    }

    /**
     * {@inheritdoc}
     */
    public function has($identifier)
    {
        return isset($this->commands[$identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return $this->commands;
    }

    /**
     * {@inheritdoc}
     */
    public function get($identifier)
    {
        if (isset($this->commands[$identifier])) {
            return $this->commands[$identifier];
        } else {
            throw new \UnknownIdentifierException($identifier);
        }
    }
}
