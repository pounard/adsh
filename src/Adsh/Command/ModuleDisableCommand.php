<?php

namespace Adsh\Command;

use Adsh\Drupal\LocalSite;
use Adsh\Drupal\SiteInterface;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleDisableCommand extends SiteCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('site', 's',
                    InputOption::VALUE_OPTIONAL, "Site to operate on"),
                new InputOption('module', 'm',
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, "Module(s) to disable"),
                new InputOption('uninstall', null,
                    InputOption::VALUE_NONE, "Force modules uninstall (also work with already disabled modules)"),
                new InputOption('nodeps', null,
                    InputOption::VALUE_NONE, "Ignore dependencies (may break your site)"),
            ))
            ->setName('module-disable')
            ->setAliases(array('md'))
            ->setDescription('Disable module(s)')
            ->setHelp("The <info>module-disable</info> command allows you to disable module(s).");
    }

    /**
     * {@inheritdoc}
     */
    protected function executeOnSite(
        InputInterface $input, OutputInterface $output, SiteInterface $site)
    {
        $site->bootstrap();

        $enabled         = array();
        $modules         = $input->getOption('module');
        $disabled        = array();
        $toUninstall     = array();
        $doUninstall     = $input->getOption('uninstall');
        $useDependencies = !$input->getOption('nodeps');

        // First check that modules exists
        foreach ($modules as $module) {
            if (module_exists($module)) {
                $enabled[] = $module;
            } else {
                $disabled[] = $module;
            }
        }

        // Check that modules need uninstall
        if (!empty($disabled)) {
            $toUninstall = db_select('system', 's')
                ->fields('s', array('name'))
                ->condition('s.type', 'module')
                ->condition('s.name', array($disabled), 'IN')
                ->isNotNull('s.schema_version')
                ->condition('s.schema_version', 0, '>')
                ->execute()
                ->fetchCol();
        }

        if (empty($enabled) && (!$doUninstall || empty($toUninstall))) {
            $output->writeln("<comment>All modules are already disabled, nothing to do</comment>");
            return;
        }

        if (!empty($disabled)) {
            $output->writeln(sprintf("<comment>Some modules are already disabled: %s</comment>", implode(', ', $disabled)));
        }

        foreach ($enabled as $module) {
            // Errors can happen, but Drupal does not allow any error control
            // when doing this operation
            module_disable(array($module), $useDependencies);
            $output->writeln(sprintf("<info>Module disabled: %s</info>", $module));
            $toUninstall[] = $module;
        }

        if ($doUninstall && !empty($toUninstall)) {

            // Specific fix due to Drupal wrong API
            if ($site instanceof LocalSite) {
                $site->requireFile('install.inc');
            }

            foreach ($toUninstall as $module) {
                if (drupal_uninstall_modules(array($module), $useDependencies)) {
                    $output->writeln(sprintf("<info>Module uninstalled: %s</info>", $module));
                } else {
                    $output->writeln(sprintf("<error>Module could not be uninstalled: %s</error>", $module));
                }
            }
        }
    }
}
