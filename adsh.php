<?php

use Adsh\Application;
use Adsh\Command\CacheClearCommand;
use Adsh\Command\ListSitesCommand;
use Adsh\Command\ModuleDisableCommand;
use Adsh\Command\ModuleEnableCommand;
use Adsh\Configuration\PhpRegistry;
use Adsh\Configuration\SiteRegistryCollection;

use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\ArgvInput;

require __DIR__ . '/vendor/autoload.php';

// Instanciate the $HOME config per default
$registry = new SiteRegistryCollection(); 
$registry->addRegistry(new PhpRegistry(), 'home');

$console = new Application();
$console->add(new HelpCommand());

// FIXME: Needs a lazy/cachable instanciation mecanism 
$console->add(new CacheClearCommand())->setRegistry($registry);
$console->add(new ListSitesCommand())->setRegistry($registry);
$console->add(new ModuleDisableCommand())->setRegistry($registry);
$console->add(new ModuleEnableCommand())->setRegistry($registry);

// Allow devel operations
// FIXME: Using this with command declaring strict definition will break
// input validation and make the Console component throw exceptions
$input = new ArgvInput();
if ($input->hasParameterOption(array('--debug'))) {
    $console->setCatchExceptions(false);
}

// Prey for the user him fool probably did a typo!
$console->run($input);
