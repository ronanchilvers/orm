<?php

namespace Ronanchilvers\Db\Console;

use Ronanchilvers\Db\Console\Command;
use RuntimeException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Base console application
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Application extends BaseApplication
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * Class constructor
     *
     * @param string $name
     * @param string $version
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $dispatcher = new EventDispatcher;
        $this->setDispatcher($dispatcher);
        $dispatcher->addListener(
            ConsoleEvents::COMMAND,
            [$this, 'onConsoleCommand']
        );
    }

    /**
     * Get the config array for the application
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function config($key, $default = null)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Overridden to allow us to trap the global configuration
     *
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        if (!$event->getCommand() instanceof Command) {
            return;
        }
        $input = $event->getInput();
        $cwd = getcwd();
        $configPath = null;
        if ($input->getOption('config')) {
            $configPath = $input->getOption('config');
        } else if (file_exists($cwd . '/db.config.php')) {
            $configPath = $cwd . '/db.config.php';
        }
        if (!is_null($configPath)) {
            if (!file_exists($configPath)) {
                throw new RuntimeException(
                    sprintf('Configuration file %s doesn\'t exist', $configPath)
                );
            }
            $config = include($configPath);
            if (is_array($config)) {
                $this->config = $config;
            }
        }
    }
}
