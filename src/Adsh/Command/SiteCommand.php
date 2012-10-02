<?php

namespace Adsh\Command;

use Adsh\Drupal\SiteInterface;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

abstract class SiteCommand extends Command
{
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
        $application = $this->getApplication();

        if (!$application instanceof \Adsh\Application) {
            throw new \LogicException("This command can only run in Adsh");
        }

        $site = $application->getSite();
        $output->writeln(sprintf("<comment>Working on: %s</comment>", (string)$site));

        $site->setEventDispatcher($application->getEventDispatcher());
        $this->executeOnSite($input, $output, $site);
    }
}
