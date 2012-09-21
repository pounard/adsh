<?php

namespace Adsh\Drupal;

use Symfony\Component\EventDispatcher\Event;

class SiteEvent extends Event implements SiteAwareInterface
{
    /**
     * @var Adsh\Drupal\SiteInterface
     */
    private $site;

    /**
     * {@inheritdoc}
     */
    final public function getSite()
    {
        if (null === $this->site) {
            throw new LogicException("Site is not set");
        }

        return $this->site;
    }

    /**
     * {@inheritdoc}
     */
    final public function setSite(SiteInterface $site)
    {
        $this->site = $site;
    }
}
