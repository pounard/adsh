<?php

namespace Adsh\Configuration;

/**
 * Allow to use multiple site registries at once
 *
 * Identifiers may collide between registries, the first one set wins
 */
class SiteRegistryCollection extends AbstractRegistryCollection implements SiteRegistryInterface
{
    public function __construct()
    {
        parent::__construct('\Adsh\Configuration\SiteRegistryInterface');
    }
}
