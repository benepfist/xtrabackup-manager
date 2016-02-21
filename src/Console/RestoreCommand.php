<?php

namespace Benepfist\Xtrabackup\Manager\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreCommand extends BaseCommand
{
    /**
     * configure command arguments and options
     *
     * @return  void
     */
    public function configure()
    {
        parent::configure();

        $this->setName('restore')
             ->setDescription('restore database')
             ->addOption('user', null, InputOption::VALUE_OPTIONAL, 'The database user')
             ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'The database password.')
             ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Remote host')
             ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'The database port');
    }

    /**
     * execute restore command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $backup = date('Y_m_d_His');
        $restore_dir = $this->option('restore-dir');

        // Step 1) Backup datadir
        $this->info("Backup datadir ...");
        $this->runProcess("mkdir -p /tmp/xbm/backup/ && mv /var/lib/mysql /tmp/xbm/backup/$backup");

        // Step 2) Restore latest backup
        $this->info("Restore backup ...");
        $this->runProcess("innobackupex --copy-back {$restore_dir}/current");

        // Step 3) Modify datadir permission
        $this->info("Modify Permission ...");
        $this->runProcess("sudo chown -R mysql: /var/lib/mysql");

        // Step 4) Restart mysql service
        $this->info("Restart mysql ...");
        $this->runProcess("sudo service mysql start");
    }
}
