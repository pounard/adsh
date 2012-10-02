<?php

namespace Adsh\Configuration;

/**
 * Minimal requirements for a registry to be usable with abstract helpers.
 */
interface RegistryInterface
{
    /**
     * Tell the identifier exists
     *
     * @param scalar $identifier Site identifier
     */
    public function has($identifier);

    /**
     * Get site list
     *
     * @return array Key/value pairs of registered objects while keys are
     *               internal identifier scalar values
     */
    public function getAll();

    /**
     * Get a single site instance
     *
     * @param scalar $identifier         Registered object identifier
     *
     * @return mixed                     Registered object under this name
     *
     * @throws UnknownIdentifierException If identifier is unknown
     */
    public function get($identifier);
}
