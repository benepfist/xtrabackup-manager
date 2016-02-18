<?php

namespace Benepfist\Xtrabackup\Manager\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{

    /**
     * configure the command options
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('test')
             ->setDescription('Test Xtrabackup Manager');
    }

    /**
     * Execute the command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $process = new Process("echo 'test'");

        try {
            $process->mustRun(function ($type, $line) use ($output) {
                if (Process::ERR === $type) {
                    $output->error($line);
                } else {
                    $output->write($line);
                }
            });
        } catch (ProcessFailedException $e) {
            return 1;
        }

        return 0;
    }
}
