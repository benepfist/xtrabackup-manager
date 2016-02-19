<?php

namespace Benepfist\Xtrabackup\Manager\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
             ->addOption('restore-dir', '/var/backups/restore', InputOption::VALUE_REQUIRED, 'Restore directory')
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
        $restore_dir = ($restore = $input->getOption('restore-dir')) ? $restore : $this->getRestoreDirectory();

        // Step 1) Backup datadir
        $output->writeln("<info>Backup datadir ...</info>");
        $this->runProcess(new Process("mkdir -p /tmp/xbm/backup/ && mv /var/lib/mysql /tmp/xbm/backup/$backup"), $input, $output);

        // Step 2) Restore latest backup
        $output->writeln("<info>Restore backup ...</info>");
        $this->runProcess(new Process("innobackupex --copy-back {$restore_dir}/current"), $input, $output);

        // Step 3) Modify datadir permission
        $output->writeln("<info>Modify Permission ...</info>");
        $this->runProcess(new Process("sudo chown -R mysql: /var/lib/mysql"), $input, $output);

        // Step 4) Restart mysql service
        $output->writeln("<info>Restart mysql ...</info>");
        $this->runProcess(new Process("sudo service mysql start"), $input, $output);
    }
}
