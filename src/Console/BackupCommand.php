<?php

namespace Benepfist\Xtrabackup\Manager\Console;

use Benepfist\Xtrabackup\Manager\Utils\ParameterParser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BackupCommand extends BaseCommand
{
    /**
     * configure the command options
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('backup')
             ->setDescription('Takes a Fullbackup')
             ->addArgument('backup-dir', InputArgument::REQUIRED, 'Backup directory')
             ->addOption('user', null, InputOption::VALUE_OPTIONAL, 'The database user')
             ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'The database password.')
             ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Remote host')
             ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'The database port')
             ->addOption('rsync', null, InputOption::VALUE_NONE, 'Use rsync utiltiy to optimize local file transfer');
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
        $backup = date('Y_m_d_His');
        $backup_dir = $this->argument('backup-dir');
        $restore_dir = $this->option('restore-dir');
        $backup_path = "{$backup_dir}/{$backup}";
        $releases_dir = "$restore_dir/releases";
        $restore_path = "{$restore_dir}/current";

        // Step 1) Backup Database
        $this->takeFullBackup($backup_path);

        // Step 2) Copy last backup to restore directory and symling it to current folder
        $this->info("Copy backup for quick restore ...");
        $this->copyBackup($backup_path, $releases_dir);

        // Step 3) Symling restore_directory to current folder
        $this->info("Link backup to restore");
        $this->linkDirectory("{$releases_dir}/{$backup}/", "{$restore_path}/");

        // Step 4) Prepare Backup for restore
        $this->prepareForRestore($restore_path);

        $this->info("Backup finished!");
    }
}
