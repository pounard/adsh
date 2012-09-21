<?php

namespace Adsh\Configuration;

/**
 * Site registry interface.
 * 
 * A site registry may handle multiple sites, each sites having an internal
 * scalar identifier, whose form can vary depending on the implementation
 */
interface SiteRegistryInterface extends \Countable
{
    /**
     * Get site list
     *
     * @return array Key/value pairs of Adsh\Drupal\SiteInterface instances
     *               keys being internal site identifier scalar values
     */
    public function getList();

    /**
     * Get a single site instance
     *
     * @param scalar $identifier         Site identifier
     *
     * @return Adsh\Drupal\SiteInterface Site
     *
     * @throws UnknownSiteException      If identifier is unknown
     */
    public function getInstance($identifier);

    /**
     * Tell the identifier exists
     *
     * @param scalar $identifier Site identifier
     */
    public function has($identifier);
}
