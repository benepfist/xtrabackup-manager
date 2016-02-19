<?php

namespace Benepfist\Xtrabackup\Manager\Console;

use Benepfist\Xtrabackup\Manager\Utils\ParameterParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class BaseCommand extends Command
{
    /**
     * init command
     *
     * @param Process $process
     * @param string|null  $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * add debug option to all commands
     *
     * @return [type]
     */
    protected function configure()
    {
        $this->addOption('debug', false, InputOption::VALUE_NONE, 'debug command (only for testing)');
    }

    /**
     * run process
     *
     * @param  Process $process
     * @param  InputInterface $input
     * @param  OutputInterface $output
     * @param  string $errorMessage
     *
     * @return boolean
     */
    public function runProcess($process, InputInterface $input, OutputInterface $output, $errorMessage = "")
    {
        if ($input->getOption('debug')) {
            $command = $process->getCommandLine();
            $output->writeln("<info>... {$command} ...</info>");
        } else {

            $this->testIfInnobackupexInstalled();

            try {
                $process->mustRun(function ($type, $line) use ($output) {
                    if (Process::ERR === $type) {
                        $output->writeln("<error>{$line}</error>");
                    } else {
                        $output->writeln("<error>{$line}</error>");
                    }
                });
            } catch (ProcessFailedException $e) {
                $output->writeln("<error>{$errorMessage}</error>");
                return 1;
            }
        }
        return 0;
    }

    /**
     * test if innobackupex is installed
     *
     * @return void
     */
    protected function testIfInnobackupexInstalled()
    {
        $exitCode = (new Process('innobackupex -v'))->run();

        switch ($exitCode) {
            case '127':
                throw new RuntimeException('Xtrabackup is not installed. Please install xtrabackup first!');
                break;
        }
    }

    /**
     * get restore directory
     *
     * @return string
     */
    protected function getRestoreDirectory()
    {
        return "/var/backups/restore/";
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $cap
     * @return string
     */
    public static function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');
        return preg_replace('/(?:'.$quoted.')+$/', '', $value).$cap;
    }
}
