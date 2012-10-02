<?php

namespace Adsh\Configuration;

/**
 * Base implement for all registry collection.
 */
abstract class AbstractRegistryCollection implements RegistryInterface
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
     * Interface or class name the registries must implement/subclass
     *
     * @var string
     */
    protected $interfaceName;

    /**
     * Default constructor
     *
     * @param string $interfaceName Interface or class name the registries must
     *                              implement to be valid for this collection
     */
    public function __construct($interfaceName = null)
    {
        $this->interfaceName = $interfaceName;
    }

    /**
     * Add registry to the chain
     *
     * @param mixed              $registry Registry instance
     * @param string $alias      Alias
     * @throws \RuntimeException If alias already exists
     */
    final public function addRegistry($registry, $alias = null)
    {
        if ((isset($this->interfaceName) && !$registry instanceof $this->interfaceName) ||
            !$registry instanceof RegistryInterface)
        {
            throw new \InvalidArgumentException(sprintf(
                "Given parameter does not extends nor implements %s",
                $this->interfaceName));
        }

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
     * Find identifier from list
     *
     * @return string                      Aboslute identifier prefixed with
     *                                     registry alias
     * @throws AmbigousIdentifierException If no registry with the provided
     *                                     alias is not found or if the given
     *                                     identifier is not prefixed with a
     *                                     registry alias and matches more than
     *                                     one sites into more than one
     *                                     registries
     * @throws UnknownIdentifierException  If identifier does not match anything
     */
    final protected function desambiguateIdentifier($identifier)
    {
        if (strpos($identifier, self::ALIAS_SEPARATOR)) {
            list($alias, $localIdentifier) = explode(self::ALIAS_SEPARATOR, $identifier, 2);

            if (!isset($this->registries[$alias])) {
                throw new AmbigousIdentifierException(sprintf(
                    "Could not find the registry: %s", $alias));
            }

            if (!$this->registries[$alias]->has($localIdentifier)) {
                throw new UnknownIdentifierException($identifier);
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
                throw new UnknownIdentifierException($identifier);
            } else if (1 < count($found)) {
                throw new AmbigousIdentifierException(sprintf(
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
    final public function has($identifier)
    {
        try {
            $this->desambiguateIdentifier($identifier);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function getAll()
    {
        $ret = array();

        foreach ($this->registries as $alias => $registry) {
            foreach ($registry->getAll() as $identifier => $site) {
                $ret[$alias . self::ALIAS_SEPARATOR . $identifier] = $site;
            }
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    final public function get($identifier)
    {
        $identifier = $this->desambiguateIdentifier($identifier);

        list($alias, $identifier) = explode(self::ALIAS_SEPARATOR, $identifier, 2);

        return $this->registries[$alias]->get($identifier);
    }
}
