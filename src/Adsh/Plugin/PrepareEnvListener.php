<?php

namespace Adsh\Plugin;

use Adsh\Drupal\LocalSite;
use Adsh\Drupal\SiteEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PrepareEnvListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            LocalSite::EVENT_ENV_PREPARE => array('onPrepareEnv', 128),
        );
    }

    /**
     * Set basic Drupal environment
     */
    public function onPrepareEnv(SiteEvent $event)
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }
}
