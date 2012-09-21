<?php

namespace Adsh\Configuration;

/**
 * Allow to use multiple site registries at once
 *
 * Identifiers may collide between registries, the first one set wins
 */
class SiteRegistryCollection implements SiteRegistryInterface
{
    /**
     * Registry alias / site name separator
     */
    const ALIAS_SEPARATOR = '/';

    /**
     * List of Adsh\Configuration\SiteRegistryInterface instances
     *
     * @var array
     */
    protected $registries = array();

    /**
     * Add registry to the chain
     *
     * @param SiteRegistryInterface $registry Registry instance
     * @param string $alias                   Alias
     * @throws \RuntimeException              If alias already exists
     */
    public function addRegistry(SiteRegistryInterface $registry, $alias = null)
    {
        if (null === $alias) {
            $alias = get_class($registry);
        }

        if (isset($this->registries[$alias])) {
            throw new \RuntimeException(
                "Registry with alias %s already exists", $alias);
        }

        $this->registries[$alias] = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $ret = array();

        foreach ($this->registries as $alias => $registry) {
            foreach ($registry->getList() as $identifier => $site) {
                $ret[$alias . self::ALIAS_SEPARATOR . $identifier] = $site;
            }
        }

        return $ret;
    }

    /**
     * Find identifier from list
     *
     * @return string                Aboslute identifier prefixed with registry
     *                               alias which contains the site
     * @throws AmbigousSiteException If no registry with the provided alias is
     *                               not found or if the given identifier is not
     *                               prefixed with a registry alias and matches
     *                               more than one sites into more than one
     *                               registries
     * @throws UnknownSiteException  If identifier does not match anything
     */
    protected function desambiguateIdentifier($identifier)
    {
        if (strpos($identifier, self::ALIAS_SEPARATOR)) {
            list($alias, $localIdentifier) = explode(self::ALIAS_SEPARATOR, $identifier, 2);

            if (!isset($this->registries[$alias])) {
                throw new AmbigousSiteException(sprintf(
                    "Could not find the registry: %s", $alias));
            }

            if (!$this->registries[$alias]->has($localIdentifier)) {
                throw new UnknownSiteException($identifier);
            }
            // Identifier is OK, just let the function return gracefully
        } else {
            // No separator found, name could be ambigous
            $found = array();
            foreach ($this->registries as $alias => $registry) {
                if ($registry->has($identifier)) {
                    $found[] = $alias . self::ALIAS_SEPARATOR . $identifier;
                }
            }

            if (empty($found)) {
                throw new UnknownSiteException($identifier);
            } else if (1 < count($found)) {
                throw new AmbigousSiteException(sprintf(
                    "Identifier conflict for %s, can be one of: %s",
                    $identifier, implode(', ', $found)));
            } else {
                // Our real identifier is different from the given one,
                // desambiguate it properly
                $identifier = reset($found);
            }
        }

        return $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($identifier)
    {
        $identifier = $this->desambiguateIdentifier($identifier);

        list($alias, $identifier) = explode(self::ALIAS_SEPARATOR, $identifier, 2);

        return $this->registries[$alias]->getInstance($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function has($identifier)
    {
        $this->desambiguateIdentifier($identifier);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $count = 0;

        foreach ($this->registries as $registry) {
            $count += $registry->count();
        }

        return $count;
    }
}
