<?php

namespace Adsh\Drupal;

use Adsh\EventDispatcher\EventDispatcherAware;
use Adsh\Exception as AdshException;
use Adsh\Plugin\PrepareEnvListener;

class LocalSite extends EventDispatcherAware implements SiteInterface
{
    /**
     * Regex for finding Drupal version in bootstrap.inc file
     */
    const VERSION_PHP_REGEX = "/^\s*define\('VERSION', '([^']+)'/ims";

    /**
     * Event raised before bootstrap
     */
    const EVENT_ENV_PREPARE = 'local:env:prepare';

    /**
     * Event raised right after configuration has been loaded
     */
    const EVENT_BOOTSTRAP_POST_CONFIGURE = 'local:boot:postconfig';

    /**
     * Event raised right after site has been fully bootstrapped
     */
    const EVENT_BOOTSTRAP_POST_FULL = 'local:boot:postfull';

    /**
     * Find the local instance we are into
     *
     * @throws \RuntimeException If the command is not run into a site
     */
    public static function findLocalInstance()
    {
        // Using DIRECTORY_SEPARATOR is mandatory because of WINNT
        // environments
        $parts = explode(DIRECTORY_SEPARATOR, getcwd());

        while (!empty($parts)) {
            $dirpath = implode(DIRECTORY_SEPARATOR, $parts);

            if (file_exists($dirpath . '/index.php')) {
                // We found the site root, instanciate the site and return
                // gracefully the instance
                // FIXME: Is finding the index.php file is sufficient?
                $site = new self();
                $site->setSiteRoot($dirpath);

                // FIXME: Handle multisite
                return $site;
            }

            array_pop($parts);
        }

        throw new \RuntimeException(sprintf(
            "No Drupal site found in the current working path"));
    }

    /**
     * Site identifier for multisite installation
     *
     * @var string
     */
    private $siteIdentifier = 'default';

    /**
     * Site root path
     *
     * @var string
     */
    private $siteroot;

    /**
     * Site URL
     *
     * @var string
     */
    private $siteUrl;

    /**
     * Site version
     *
     * @var string
     */
    private $version;

    /**
     * Is site bootstrapped
     *
     * @var bool
     */
    private $bootstrapped = false;

    /**
     * Is this instance locked
     *
     * @var bool
     */
    private $locked = false;

    /**
     * The URL could not be determined by the findUrl method
     *
     * @var bool
     */
    protected $urlCannotBeFound = false;

    /**
     * The event dispatcher has been prepared
     *
     * @var bool
     */
    private $eventDispatcherPrepared = false;

    /**
     * Force a file to be included
     *
     * @param string $name Core file, e.g. 'cache.inc'
     */
    final public function requireFile($name)
    {
        $version = $this->getVersion();

        if ($version < 8) {
            $filename = $this->getSiteRoot() . '/includes/' . $name;
        } else {
            $filename = $this->getSiteRoot() . '/core/includes/' . $name;
        }

        if (!file_exists($filename)) {
            throw new \RuntimeException(sprintf(
                "File %s does not exists in this site", $name));
        }

        require_once $filename;
    }

    /**
     * Get Drupal site file contents or partial contents
     *
     * @param string $filename  File name, relative to site root
     * @param bool $coreFile    If set to true, will also lookup prefixing by
     *                          the 'core' depending on Drupal version
     * @param int $bufferLength Buffer size to load
     * @return string           File contents
     */
    protected function getFileBuffer($filename,
        $coreFile = true, $bufferLength = null)
    {
        if (!isset($this->siteroot)) {
            throw new \LogicException(
                "Cannot scan Drupal files without a site root set");
        }

        // Attempt to guess directory structure
        if ($coreFile && is_dir($this->siteroot . '/core')) {
            // This is a Drupal 8 or superior
            $filename = $this->siteroot . '/core/' . $filename;
        } else {
            // This a Drupal 7 or inferior
            $filename = $this->siteroot . '/' . $filename;
        }

        if (!file_exists($filename)) {
            throw new \RuntimeException(sprintf(
                "Cannot find file: %s", $filename));
        }

        if (null !== $bufferLength) {
            if (!$handle = fopen($filename, "r")) {
                throw new \RuntimeException(
                    sprint("Cannot open file for reading: %s", $filename));
            }

            $buffer = fread($handle, $bufferLength);
            fclose($handle);
        } else {
            if (!$buffer = file_get_contents($filename)) {
                throw new \RuntimeException(
                    sprint("Cannot open file for reading: %s", $filename));
            }
        }

        return $buffer;
    }

    /**
     * Attempt to find Drupal version on a non bootstrapped site
     *
     * @return string            Drupal version constant value
     * @throws \RuntimeException If version could not be found
     */
    protected function findVersion()
    {
        // VERSION constant is always approximatively in the first 20 lines,
        // assuming that 1024 is enough
        $buffer = $this->getFileBuffer(
            'includes/bootstrap.inc', true, 1024);

        $matches = array();

        if (!preg_match(self::VERSION_PHP_REGEX, $buffer, $matches)) {
            throw new \RuntimeException(sprintf(
                "Could not find the VERSION constant in file: %s", $filename));
        }

        return $matches[1];
    }

    /**
     * Attempt to find Drupal URL on a non bootstrapped site
     *
     * @return string            Site URL
     * @throws \RuntimeException If URL could not be found
     */
    protected function findUrl()
    {
        if ($this->bootstrapped) {
            // FIXME: FIND THE URL DEPENDING ON DRUPAL VERSION
            throw new \BadMethodCallException("Not implemented yet");
        } else {
            // Try to find the according settings.php file 
            // FIXME: This will change for D8

        }

        // As soon as you try to find the URL, the site is considered as
        // locked
        $this->locked = true;
    }

    /**
     * {@inheritdoc}
     */
    final public function getVersion()
    {
        if (!isset($this->version)) {
            if (!$this->bootstrapped) {
                $this->version = $this->findVersion();
            } else {
                throw new \RuntimeException(
                    "Site is bootstrapped yet we have no version");
            }
        }

        return $this->version;
    }

    /**
     * Set version
     *
     * @param string $version
     */
    final protected function setVersion($version)
    {
        if ($this->isLocked()) {
            throw new SiteLockedException(
                "Cannot change site version of a locked instance");
        }

        $this->version = $version;
    }

    /**
     * {@inheritdoc}
     */
    final public function getSiteIdentifier()
    {
        return $this->siteIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    final public function setSiteIdentifier($identifier)
    {
        if ($this->isLocked()) {
            throw new SiteLockedException(
                "Cannot change site identifier of a locked instance");
        }

        $this->siteIdentifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    final public function getSiteRoot()
    {
        if (null === $this->siteroot) {
            throw new \LogicException("Site root is not set");
        }

        return $this->siteroot;
    }

    /**
     * {@inheritdoc}
     */
    final public function setSiteRoot($path)
    {
        if ($this->isLocked()) {
            throw new SiteLockedException(
                "Cannot change site root of an already bootstrapped instance");
        }

        $this->siteroot = $path;
    }

    /**
     * {@inheritdoc}
     */
    final public function getUrl()
    {
        if (null === $this->siteUrl) {
            if ($this->urlCannotBeFound) {
                throw new SiteHasNoUrlException(
                    "The URL of this site cannot be determined automatically");
            } else {
                try {
                    $this->siteUrl = $this->findUrl();
                } catch (AdshException $e) {
                    throw new SiteHasNoUrlException(
                        "The URL of this site cannot be determined automatically",
                        $e->getCode(), $e);

                    $this->urlCannotBeFound = true;
                }
            }
        }

        return $this->siteUrl;
    }

    /**
     * {@inheritdoc}
     */
    final public function setUrl($url)
    {
        if ($this->isLocked()) {
            throw new \LogicException(
                "Cannot change site URL of an already bootstrapped instance");
        }

        $this->siteUrl = $url;
    }

    /**
     * {@inheritdoc}
     */
    final public function isBootstrapped()
    {
        return $this->bootstrapped;
    }

    final public function isLocked()
    {
        return $this->locked;
    }

    /**
     * Lock this instance, once done, this cannot be unlocked anymore
     */
    final protected function lock()
    {
        $this->locked = true;
    }

    /**
     * Mark this instance as bootstrapped and lock it, once done, this cannot
     * undone anymore
     */
    final protected function markAsBootstrapped()
    {
        $this->locked = true;
        $this->bootstrapped = true;
    }

    /**
     * Prepare event dispatcher
     */
    final protected function prepareEventDispatcher()
    {
        if ($this->eventDispatcherPrepared) {
            return;
        }

        $this->eventDispatcherPrepared = true;

        $dispatcher = $this->getEventDispatcher();
        $dispatcher->addSubscriber(new PrepareEnvListener());
    }

    /**
     * Raise site event using the current instance as site
     *
     * @param string $eventName Event name
     */
    final protected function raiseSiteEvent($eventName)
    {
        $event = new SiteEvent();
        $event->setSite($this);

        $this->getEventDispatcher()->dispatch($eventName, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($mode = SiteInterface::BOOTSTRAP_FULL)
    {
        $siteroot = $this->getSiteRoot();
        $this->prepareEventDispatcher();

        if (is_dir($siteroot . '/core')) {
            // Drupal version is 8 or superior
            $siteroot .= '/core';
        }

        if (!file_exists($siteroot . '/includes/bootstrap.inc')) {
            // FIXME: D8 might change this
            throw new \LogicException(sprintf(
                "Site is not a Drupal site: %s", $siteroot));
        }

        // Some minor modifications are mandatory here, do not use the local
        // variables because they have been altered
        chdir($siteroot);
        define('DRUPAL_ROOT', $siteroot);

        $this->raiseSiteEvent(self::EVENT_ENV_PREPARE);

        $this->requireFile('bootstrap.inc');
        $this->setVersion(VERSION);

        // Site root has been set up, we cannot go back anymore
        $this->lock();

        switch ($mode) {

            case SiteInterface::BOOTSTRAP_CONFIGURATION:
                $mode = DRUPAL_BOOTSTRAP_CONFIGURATION;
                break;

            case SiteInterface::BOOTSTRAP_DATABASE:
                $mode = DRUPAL_BOOTSTRAP_DATABASE;
                break;

            case SiteInterface::BOOTSTRAP_VARIABLES:
                $mode = DRUPAL_BOOTSTRAP_VARIABLES;
                break;

            case SiteInterface::BOOTSTRAP_FULL:
                $mode = DRUPAL_BOOTSTRAP_FULL;
                break;

            case SiteInterface::BOOTSTRAP_KERNEL:
                throw new \RuntimeException(
                    "Kernel level bootstrap is not supported yet");
                break;
        }

        // self::bootstrapDrupal(DRUPAL_BOOTSTRAP_CONFIGURATION);
        drupal_bootstrap(DRUPAL_BOOTSTRAP_CONFIGURATION);
        $this->raiseSiteEvent(self::EVENT_BOOTSTRAP_POST_CONFIGURE);

        if ($mode < DRUPAL_BOOTSTRAP_DATABASE) {
            return;
        }

        // self::bootstrapDrupal(DRUPAL_BOOTSTRAP_DATABASE);
        drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);

        if ($mode < DRUPAL_BOOTSTRAP_VARIABLES) {
            return;
        }

        // self::bootstrapDrupal(DRUPAL_BOOTSTRAP_VARIABLES);
        drupal_bootstrap(DRUPAL_BOOTSTRAP_VARIABLES);

        if ($mode < DRUPAL_BOOTSTRAP_FULL) {
            return;
        }

        // FIXME: D8 is different
        // self::bootstrapDrupal(DRUPAL_BOOTSTRAP_FULL);
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

        $this->markAsBootstrapped();
        $this->raiseSiteEvent(self::EVENT_BOOTSTRAP_POST_FULL);
    }

    public function __toString()
    {
        return sprintf("%s [%s]",
            $this->getSiteRoot(), $this->getSiteIdentifier());
    }
}
