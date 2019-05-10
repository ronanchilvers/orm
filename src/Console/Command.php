<?php
namespace Ronanchilvers\Db\Console;

use RuntimeException;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends BaseCommand
{
    /**
     * Get a configuration parameter
     *
     * @param Symfony\Component\Console\Input\InputInterface $input
     * @param string $key
     * @param string $default
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function config($key, $default = null)
    {
        return $this->getApplication()->config($key, $default);
    }

    /**
     * Get an option from input or from config
     *
     * @param Symfony\Component\Console\Input\InputInterface $input
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function optionOrConfig(InputInterface $input, $key, $default = null)
    {
        if (null !== $input->getOption($key)) {
            return $input->getOption($key);
        }

        return $this->config($key, $default);
    }
}
