<?php

namespace Adsh;

use Adsh\EventDispatcher\EventDispatcherAwareInterface;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Application extends ConsoleApplication implements
    EventDispatcherAwareInterface
{
    /**
     * @var Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * List of Adsh\Configuration\CommandRegistryInterface instances
     *
     * @var array
     */
    private $commandProviders = array();

    /**
     * {@inheritdoc}
     */
    final public function setEventDispatcher(
        EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function registerCommandProvider(CommandRegistryInterface $commandRegistry)
    {
        throw new \BadFunctionCallException("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    final public function getEventDispatcher()
    {
        if (null === $this->eventDispatcher) {
            $this->eventDispatcher = new EventDispatcher();
        }

        return $this->eventDispatcher;
    }
}
