<?php

namespace Adsh\Command;

use Adsh\Drupal\SiteInterface;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleEnableCommand extends SiteCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('site', 's',
                    InputOption::VALUE_REQUIRED, "Site to operate on"),
                new InputOption('modules', 'm',
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, "Module(s) to enable"),
            ))
            ->setName('module-enable')
            ->setAliases(array('me'))
            ->setDescription('Enable module(s)')
            ->setHelp("The <info>module-enable</info> command allows you to enable module(s).");
    }

    /**
     * {@inheritdoc}
     */
    protected function executeOnSite(
        InputInterface $input, OutputInterface $output, SiteInterface $site)
    {
        $site->bootstrap();

        $enabled             = array();
        $modules             = $input->getOption('modules');
        $missingModules      = array();
        $missingDependencies = array();
        $alreadyEnabled      = array();
        $dialogHelper        = $this->getHelper('dialog');

        // First check that modules exists
        foreach ($modules as $module) {
            if (module_exists($module)) {
                $alreadyEnabled[] = $module;
            }
        }
        $modules = array_diff($modules, $alreadyEnabled);

        if (empty($modules)) {
            $output->writeln("<comment>All modules are already enabled, nothing to do</comment>");
            return;
        }

        if (!empty($alreadyEnabled)) {
            $output->writeln(sprintf("<comment>Some modules are already enabled: %s</comment>",
                implode(', ', $alreadyEnabled)));
        }

        // If some modules don't exists ask the user if he wants to continue
        $missingModules = db_select('system', 's')
            ->fields('s', array('name'))
            ->condition('s.type', 'module')
            ->condition('s.name', $modules, 'IN')
            ->execute()
            ->fetchCol();
        $missingModules = array_diff($modules, $missingModules);

        if (!empty($missingModules)) {
            $output->writeln(sprintf("<error>Some modules are missing: %s</error>",
                implode(',', $missingModules)));

            if (!$dialogHelper->askConfirmation($output,
                "Do you want to continue anyway? (y/N) ", false))
            {
                $output->writeln("<error>User cancelation</error>");
                return;
            }
        }

        // User wants to continue
        $modules = array_diff($modules, $missingModules);

        if (empty($modules)) {
            $output->writeln("<comment>No modules left to enable, exiting</comment>");
            return;
        }

        foreach ($modules as $module) {
            if (module_enable(array($module))) {
                $output->writeln(sprintf("<info>Module enabled: %s</info>", $module));
            } else {
                $output->writeln(sprintf("<error>Module could not be enabled: %s</error>",
                    $module));

                if (!$dialogHelper->askConfirmation($output,
                    "Do you want to continue? (y/N) ", false))
                {
                    $output->writeln("<error>User cancelation</error>");
                    return;
                }
            }
        }
    }
}
