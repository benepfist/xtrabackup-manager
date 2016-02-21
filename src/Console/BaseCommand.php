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
     * The input interface implementation
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * The output interface implementation
     *
     * @var OutputInterface
     */
    protected $output;

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
        $this->addOption('restore-dir', null, InputOption::VALUE_REQUIRED, 'Restore directory', '/var/backups/restore')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'debug command (only for testing)');
    }

    /**
     * Run the console command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;

        $this->output = $output;

        return parent::run($input, $output);
    }

    /**
     * run process
     *
     * @param  string $command
     * @param  string $errorMessage
     *
     * @return boolean
     */
    public function runProcess($command, $errorMessage = "")
    {
        $process = new Process($command);

        if ($this->option('debug')) {
            $commandoutput = $process->getCommandLine();
            $this->info("... {$commandoutput} ...");
        } else {

            $this->testIfInnobackupexInstalled();

            try {
                $process->mustRun(function ($type, $line) {
                    if (Process::ERR === $type) {
                        $this->error($line);
                    } else {
                        $this->info($line);
                    }
                });
            } catch (ProcessFailedException $e) {
                $this->error($errorMessage);
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
     * take a full backup
     *
     * @param  string          $backup
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @return void
     */
    public function takeFullBackup($backup)
    {
        $param = implode(' ', (new ParameterParser)->getParameters($this->input));

        $this->info("Backup database ...");
        $this->runProcess("innobackupex {$backup}/ {$param} --no-timestamp");
    }

    /**
     * prepare backup for restore
     *
     * @param  string $restore_path
     *
     * @return void
     */
    protected function prepareForRestore($restore_path)
    {
        // Prepare Command for restore
        $this->info("Prepare backup for restore ...");
        $this->runProcess("innobackupex --apply-log {$restore_path}");
    }

    /**
     * copy backup
     *
     * @param  string          $source
     * @param  string          $target
     *
     * @return void
     */
    protected function copyBackup($source, $target)
    {
        $commands = [
            "[ -d {$target} ] || mkdir -p {$target}",
            "cp -R {$source} {$target}"
        ];

        $this->runProcess(implode(' && ', $commands));
    }

    /**
     * link source to target
     *
     * @param  string          $source
     * @param  string          $target
     *
     * @return void
     */
    protected function linkDirectory($source, $target)
    {
        $this->runProcess("ln -nfs {$source} {$target}");
    }

   /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @return void
     */
    public function info($string)
    {
        $this->line($string, 'info');
    }
    /**
     * Write a string as standard output.
     *
     * @param  string  $string
     * @param  string  $style
     * @return void
     */
    public function line($string, $style = null)
    {
        $styled = $style ? "<$style>$string</$style>" : $string;
        $this->output->writeln($styled);
    }


    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function error($string)
    {
        $this->line($string, 'error');
    }

    /**
     * Get the value of a command argument.
     *
     * @param  string  $key
     * @return string|array
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }
        return $this->input->getArgument($key);
    }

    /**
     * Get the value of a command option.
     *
     * @param  string  $key
     * @return string|array
     */
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }
        return $this->input->getOption($key);
    }
}
