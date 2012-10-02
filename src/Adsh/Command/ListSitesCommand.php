<?php

namespace Adsh\Command;

use Adsh\Configuration\SiteRegistryAwareInterface;
use Adsh\Configuration\SiteRegistryInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListSitesCommand extends Command
{
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
        $application = $this->getApplication();

        if (!$application instanceof \Adsh\Application) {
            throw new \LogicException("This command can only run in Adsh");
        }

        foreach ($application->getRegistry()->getAll() as $identifier => $site) {
            $output->writeln(sprintf("<info>%s</info>\t%s",
                $identifier, (string)$site));
        }
    }
}
