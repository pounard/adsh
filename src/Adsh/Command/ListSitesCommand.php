<?php

namespace Adsh\Command;

use Adsh\Configuration\SiteRegistryAwareInterface;
use Adsh\Configuration\SiteRegistryInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListSitesCommand extends Command implements SiteRegistryAwareInterface
{
    /**
     * @var site Adsh\Configuration\SiteRegistryInterface
     */
    private $registry;

    /**
     * {@inheritdoc}
     */
    final public function setRegistry(SiteRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    final public function getRegistry()
    {
        if (!isset($this->registry)) {
            throw new \LogicException("No site registry set");
        }

        return $this->registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('list-sites')
            ->setAliases(array('ls'))
            ->setDescription('List all available sites')
            ->setHelp("List all available sites from enabled registries.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getRegistry()->getList() as $identifier => $site) {
            $output->writeln(sprintf("<info>%s</info>\t%s",
                $identifier, (string)$site));
        }
    }
}
