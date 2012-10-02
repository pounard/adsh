<?php

namespace Adsh\Drupal;

use Adsh\Command\CommandRegistryInterface;
use Adsh\EventDispatcher\EventDispatcherAwareInterface;

/**
 * Drupal site representation
 *
 * All methods are subject to throw SiteLockedException. If you implement this
 * interface without using the default implementation, please be careful with
 * that and throw them accordingly: any method that modifies this object
 * internals regarding code path or site access such as site root, version or
 * URL modifiers must comply to this rule
 */
interface SiteInterface extends EventDispatcherAwareInterface
{
    /**
     * Bootstrap site configuration only
     *
     * Alias of DRUPAL_BOOTSTRAP_CONFIGURATION
     */
    const BOOTSTRAP_CONFIGURATION = -1;

    /**
     * Boostrap site database only
     *
     * Alias of DRUPAL_BOOTSTRAP_DATABASE
     */
    const BOOTSTRAP_DATABASE = -2;

    /**
     * Bootstrap site database and variable system
     *
     * Alias of DRUPAL_BOOTSTRAP_VARIABLES
     */
    const BOOTSTRAP_VARIABLES = -3;

    /**
     * Bootstrap full site
     *
     * Alias of DRUPAL_BOOTSTRAP_FULL
     */
    const BOOTSTRAP_FULL = -4;

    /**
     * Bootstrap site kernel only
     *
     * This is valid only for D8 and superior
     */
    const BOOTSTRAP_KERNEL = -5;

    /**
     * Get Drupal version
     * 
     * If site is not bootstrapped, this will parse core PHP files in order to
     * find it thus may hurt performances
     *
     * @return string Drupal version number
     */
    public function getVersion();

    /**
     * Get the site identifier in case of multisite installation
     *
     * @return string Identifier
     */
    public function getSiteIdentifier();

    /**
     * Set site identifier for multisite installations
     *
     * @param string $identifier
     */
    public function setSiteIdentifier($identifier);

    /**
     * Get site root path on server
     *
     * @return string Site root path
     */
    public function getSiteRoot();

    /**
     * Set site root path on server
     *
     * @param string $path Site root path
     */
    public function setSiteRoot($path);

    /**
     * Get site URL
     *
     * If site is not bootstrapped, this will parse core PHP files in order to
     * find it thus may hurt performances
     *
     * @return string           Site URL
     *
     * @throws \DomainException If URL wasn't manually given and cannot be found 
     */
    public function getUrl();

    /**
     * Set site URL
     *
     * This is useful if you build a cache of know sites and don't want to parse
     * its file, or when you want to force a URL in case it's not findable
     *
     * @param string $url Site URL
     */
    public function setUrl($url);

    /**
     * Is the site bootstrapped
     *
     * @return bool True if bootstrapped else false
     */
    public function isBootstrapped();

    /**
     * Is the site locked for modification
     *
     * An instance is locked as soon as files are being parsed or instance has
     * been bootstrapped. This is due to the fact that any change on site
     * constants would imply that we need to unboostrap the site, which is not
     * possible due to Drupal heavy procedural orientation
     *
     * @return bool True if locked else false
     */
    public function isLocked();

    /**
     * Bootstrap the Drupal site
     *
     * @param int $mode        Level of bootstrapping
     * @return boolean         True on success
     * @throws \LogicException If another site is bootstrapped on the same
     *                         physical machine in the same script run
     */
    public function bootstrap($mode = self::BOOTSTRAP_FULL);

    /**
     * Get command registry associated to this site
     *
     * @return CommandRegistryInterface Command registry
     */
    public function getCommandRegistry();

    /**
     * Get a short textual site representation
     *
     * @return string Site textual representation
     */
    public function __toString();
}
