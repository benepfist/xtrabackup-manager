<?php

namespace Benepfist\Xtrabackup\Manager\Console;

use Benepfist\Xtrabackup\Manager\Utils\ParameterParser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
             ->addOption('rsync', false, InputOption::VALUE_NONE, 'Use rsync utiltiy to optimize local file transfer');
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
        $backup_dir = $input->getArgument('backup-dir');
        $restore_dir = $this->getRestoreDirectory();

        $param = implode(' ', (new ParameterParser)->getParameters($input));

        // Step 1) Backup Database
        $output->writeln("<info>Backup database ...</info>");
        $this->runProcess(new Process("innobackupex {$backup_dir}/{$backup}/ {$param} --no-timestamp"), $input, $output);

        // Step 2) Copy last backup to restore directory and symling it to current folder
        $output->writeln("<info>Copy backup for quick restore ...</info>");
        $commands = [
            "[ -d $restore_dir/releases ] || mkdir -p $restore_dir/releases",
            "cp -R {$backup_dir}/{$backup} $restore_dir/releases",
            "ln -nfs {$restore_dir}/releases/{$backup}/ {$restore_dir}/current"
        ];

        $this->runProcess(new Process(implode(' && ', $commands)), $input, $output);

        // Step 3) Prepare Command for restore
        $output->writeln("<info>Prepare backup for restore ...</info>");
        $this->runProcess(new Process("innobackupex --apply-log {$restore_dir}/current"), $input, $output);

        $output->writeln("<info>Backup finished!</info>");
    }
}
