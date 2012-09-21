<?php

namespace Adsh\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface EventDispatcherAwareInterface
{
    /**
     * Set new event dispatcher
     *
     * Note that this will drop all listening events
     *
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher
     */
    public function setEventDispatcher(
        EventDispatcherInterface $eventDispatcher);

    /**
     * Get event dispatcher
     *
     * @return Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher();
}
