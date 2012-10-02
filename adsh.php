<?php

use Adsh\Application;
use Adsh\Command\ListSitesCommand;
use Adsh\Configuration\PhpRegistry;
use Adsh\Configuration\SiteRegistryCollection;

use Symfony\Component\Console\Command\HelpCommand;

require __DIR__ . '/vendor/autoload.php';

// Instanciate the $HOME config per default
$registry = new SiteRegistryCollection(); 
$registry->addRegistry(new PhpRegistry(), 'home');

$console = new Application();
$console->setRegistry($registry);

// Register essential commands
$console->addCommands(array(
    new HelpCommand(),
    new ListSitesCommand(),
));

// Prey for the user
$console->run();
