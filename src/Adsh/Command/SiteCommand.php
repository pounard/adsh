<?php

namespace Adsh\Command;

use Adsh\Configuration\SiteRegistryAwareInterface;
use Adsh\Configuration\SiteRegistryInterface;
use Adsh\Drupal\LocalSite;
use Adsh\Drupal\SiteInterface;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

abstract class SiteCommand extends Command implements
    SiteRegistryAwareInterface
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
     * Implementors must implement this method completely and site parameter
     * should be used as well
     *
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('site', 's', InputOption::VALUE_OPTIONAL, "Site to operate on"),
            ));
    }

    /**
     * Execute command onto the given site
     *
     * Note that the site is not bootstrapped yet
     *
     * @param SiteInterface $site
     */
    abstract protected function executeOnSite(
        InputInterface $input, OutputInterface $output, SiteInterface $site);

    /**
     * {@inheritdoc}
     */
    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($identifier = $input->getOption('site')) {
            $site = $this->getRegistry()->getInstance($identifier);
        } else {
            // We maybe are in a local site root
            $site = LocalSite::findLocalInstance();
        }

        $application = $this->getApplication();
        if ($application instanceof \Adsh\Application) {
            $site->setEventDispatcher($application->getEventDispatcher());
        }
        $output->writeln(sprintf("<comment>Working on: %s</comment>", (string)$site));
        $this->executeOnSite($input, $output, $site);
    }
}
