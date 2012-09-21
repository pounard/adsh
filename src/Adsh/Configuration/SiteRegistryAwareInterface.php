<?php

namespace Adsh\Configuration;

interface SiteRegistryAwareInterface
{
    /**
     * Set site registry
     *
     * @param SiteRegistryInterface $registry Site registry
     */
    public function setRegistry(SiteRegistryInterface $registry);

    /**
     * Get site registry
     *
     * @return Adsh\Configuration\SiteRegistryInterface Site registry
     * @throws \LogicException                          If no registry set
     */
    public function getRegistry();
}
