<?php

namespace Benepfist\Xtrabackup\Manager\Console;

class ConfigureCommand extends BaseCommand
{
    /**
     * configure the command options
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        
        $this->setName('configure')
             ->setDescription('configure backup schemas|volumes|types')
             ->addOption('user', null, InputOption::VALUE_OPTIONAL, 'The database user')
             ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'The database password.');

             // Add option for interactive input
    }
}
