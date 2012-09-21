<?php

namespace Adsh\Drupal;

interface SiteAwareInterface
{
    /**
     * Get site
     *
     * @return Adsh\Drupal\SiteInterface Site
     */
    public function getSite();

    /**
     * Set site
     *
     * @param SiteInterface $site Site
     */
    public function setSite(SiteInterface $site);
}
