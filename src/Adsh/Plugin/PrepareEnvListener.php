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
        $site = $event->getSite();

        if (!$site instanceof LocalSite) {
            return;
        }

        // Drupal will abusively use the SCRIPT_NAME server variable, in case we
        // hit this via a third party script other than index.php, we will loose
        // the correct base_path global and have a different session name
        if (isset($_SERVER['REQUEST_METHOD']) && !empty($_SERVER['REQUEST_METHOD'])) {
            // We are in HTTP context, and need to set the SCRIPT_NAME relative
            // to DRUPAL_ROOT constant
            $prefix = substr($site->getSiteRoot(), strlen($_SERVER['DOCUMENT_ROOT']));
            $_SERVER['SCRIPT_NAME'] = '/' . $prefix . '/index.php';
        }

        // Drupal uses some $_SERVER variables from HTTPd, which we don't have
        // in cli, we need to emulate those to avoid some PHP warnings
        // This application might sometime be accessed from a web context, case
        // in which we must not override anything from the $_SERVER superglobal
        // which could alter the core behavior (it could cause session handling
        // problems for example).
        $_SERVER += array(
            'REMOTE_ADDR'     => '127.0.0.1',
            'SERVER_SOFTWARE' => 'PHP-cli',
            'REQUEST_METHOD'  => 'GET',
        );
    }
}
