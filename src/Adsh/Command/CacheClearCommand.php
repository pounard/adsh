<?php

namespace Adsh\Command;

use Adsh\Drupal\SiteInterface;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClearCommand extends SiteCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('site', 's', InputOption::VALUE_REQUIRED, "Site to operate on"),
                new InputOption('bin', 'b', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The bin to clear'),
            ))
            ->setName('cache-clear')
            ->setAliases(array('cc'))
            ->setDescription('Clear site caches')
            ->setHelp("The <info>cache-clear</info> command clear the site given site caches.");
    }

    /**
     * {@inheritdoc}
     */
    protected function executeOnSite(
        InputInterface $input, OutputInterface $output, SiteInterface $site)
    {
        $site->bootstrap();

        $bins = $input->getOption('bin');

        if (empty($bins)) {
            drupal_flush_all_caches();
            $output->writeln("<info>All site caches have been cleared.</info>");
        } else {
            foreach ($bins as $bin) {
                if ('cache' !== $bin && 0 !== strpos($bin, 'cache_')) {
                    $bin = 'cache_' . $bin;
                }
                try {
                    cache_clear_all('*', $bin, TRUE);
                    $output->writeln(sprintf("<info>Bin cleared: %s</info>", $bin));
                } catch (\Exception $e) {
                    $output->writeln(sprintf("<error>Error while clearing bin: %s</error>", $bin));
                }
            }
        }
    }
}
