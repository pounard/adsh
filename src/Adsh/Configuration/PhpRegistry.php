<?php

namespace Adsh\Configuration;

use Adsh\Configuration\SiteRegistryInterface;
use Adsh\Drupal\LocalSite;

/**
 * Uses a plain PHP file instanciating a $sites array as source, this file
 * will be per default located in $HOME/.adshrc.php
 *
 * Exemple of configuration file:
 * @code
 * $sites['my_site'] = array(
 *   'type' => 'local',
 *   'path' => '/var/www/my_site/public',
 *   'url'  => 'http://my_site.localhost',
 * );
 * @endcode
 *
 * Only the "path" and "url" keys are mandatory if the given type is "local".
 * Remote sites are not supported yet.
 */
class PhpRegistry implements SiteRegistryInterface
{
    /**
     * Default configuration file contents
     */
    const EMPTY_CONFIG_FILE = "<?php\n# Generated by Adsh\n\$sites = array();\n";

    /**
     * Get the current user home dir configuration file
     *
     * @return string File path to home default configuration file
     */
    public static function getHomeConfigurationFilename()
    {
        switch (PHP_OS) {

            case 'WINNT':
                if (isset($_SERVER['HOME'])) {
                  return $_SERVER['HOME'] . '/.adshrc.php';
                } else {
                  return getenv('HOME') . '/.adshrc.php';
                }

            default:
                // We probably are running in a UNIX environment
                return $_SERVER['HOME'] . '/.adshrc.php';
        }
    }

    /**
     * Internal sites definition array
     *
     * @var array
     */
    protected $sites;

    /**
     * Sites instances
     *
     * @var array Array of Adsh\Drupal\SiteInterface instances
     */
    protected $instances = array();

    /**
     * Does the internal list has been expanded
     * 
     * @var bool
     */
    protected $expanded = false;

    /**
     * Default constructor.
     *
     * @param string $filename File path tothe configuration
     */
    public function __construct($filename = null)
    {
        if (null === $filename) {
            $filename = self::getHomeConfigurationFilename();
        }

        if (!file_exists($filename)) {
            // This is not a fatal error, attempt to create an empty one
            if (!file_put_contents($filename, self::EMPTY_CONFIG_FILE)) {
                throw new \LogicException(
                    sprintf("Unable to create file: %s", $filename));
            }
        } else {
            // Do not attempt to parse an empty file
            $this->parseFile($filename);
        }
    }

    /**
     * Parse sites from file
     *
     * @param string $filename
     */
    protected function parseFile($filename)
    {
        if (false === @include $filename) {
            // FIXME: Replace this with a warning
            throw new \LogicException(
                sprintf("Invalid configuration file: %s", $filename));
        }

        if (!isset($sites)) {
            return;
        }

        if (!is_array($sites)) {
            // FIXME: Replace this with a warning
            throw new \LogicException(
                sprintf("Invalid configuration file: %s", $filename));
        }

        foreach ($sites as $identifier => $definition) {
            $isValid = true;
            foreach (array('path', 'url') as $property) {
                if (!isset($definition[$property])) {
                    // FIXME: Replace this with a warning
                    throw new \LogicException(sprintf(
                        "Missing property %s for site %s", $key, $identifier));
                    $isValid = false;
                }
            }

            if ($isValid) {
                $this->sites[$identifier] = $definition;
            }
        }
    }

    /**
     * Expand internal site list
     */
    protected function expandList()
    {
        if ($this->expanded) {
            return;
        }

        foreach ($this->sites as $identifier => $definition) {
            if (!isset($this->instances[$identifier])) {
                $this->get($identifier);
            }
        }

        $this->expanded = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $this->expandList();

        return $this->instances;
    }

    /**
     * {@inheritdoc}
     */
    public function get($identifier)
    {
        if (!isset($this->instances[$identifier])) {
            if (!isset($this->sites[$identifier])) {
                throw new UnknownSiteException($identifier);
            }

            $definition = $this->sites[$identifier];

            $site = new LocalSite();
            $site->setSiteRoot($definition['path']);
            $site->setUrl($definition['url']);

            $this->instances[$identifier] = $site;
        }

        return $this->instances[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function has($identifier)
    {
        return isset($this->sites[$identifier]);
    }
}
