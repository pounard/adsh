<?php

namespace Adsh;

use Adsh\Command\CacheClearCommand;
use Adsh\Command\ModuleDisableCommand;
use Adsh\Command\ModuleEnableCommand;
use Adsh\Configuration\SiteRegistryAwareInterface;
use Adsh\Configuration\SiteRegistryInterface;
use Adsh\Drupal\LocalSite;
use Adsh\Drupal\SiteAwareInterface;
use Adsh\Drupal\SiteInterface;
use Adsh\EventDispatcher\EventDispatcherAwareInterface;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Application is the only point that ties us to the global state: this is the
 * right place to tie to the working site. By design, one command will always
 * be run on one site.
 */
class Application extends ConsoleApplication implements
    EventDispatcherAwareInterface,
    SiteAwareInterface,
    SiteRegistryAwareInterface
{
    /**
     * Adsh version.
     */
    const VERSION = "0.0.1";

    /**
     * @var Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Adsh\Drupal\SiteInterface
     */
    private $site;

    /**
     * @var site Adsh\Configuration\SiteRegistryInterface
     */
    private $registry;

    /**
     * List of Adsh\Configuration\CommandRegistryInterface instances
     *
     * @var array
     */
    private $commandProviders = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct("Alternative Drupal SHell", self::VERSION);

        $this->getDefinition()->addOptions(array(
            new InputOption("debug", null,
                InputOption::VALUE_NONE, "Set debug mode"),
            new InputOption("site", "s",
                InputOption::VALUE_REQUIRED, "Work on the given site identifier"),
        ));
    }

    /**
     * {@inheritdoc}
     */
    final public function setEventDispatcher(
        EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function registerCommandProvider(CommandRegistryInterface $commandRegistry)
    {
        throw new \BadFunctionCallException("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    final public function getEventDispatcher()
    {
        if (null === $this->eventDispatcher) {
            $this->eventDispatcher = new EventDispatcher();
        }

        return $this->eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getSite()
    {
        if (!isset($this->site)) {
            $this->site = LocalSite::findLocalInstance();
        }

        return $this->site;
    }

    /**
     * {@inheritdoc}
     */
    public function setSite(SiteInterface $site)
    {
        if (isset($this->site)) {
            throw new \LogicException("A site instance already is set.");
        }

        $this->site = $site;
    }

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
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption(array('--debug'))) {
            $this->setCatchExceptions(false);
            $output->writeln("Debug mode enabled");
        }

        if ($input->hasParameterOption(array('--site', '-s'))) {
            $this->setSite($this->getRegistry()->getInstance(
                $input->getParameterOption(array('--site', '-s'))));

            // We found a site, we can therefore register commands for it
            $this->addCommands(array(
                new CacheClearCommand(),
                new ModuleDisableCommand(),
                new ModuleEnableCommand(),
            ));
        }

        parent::doRun($input, $output);
    }
}
