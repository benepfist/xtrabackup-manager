<?php

namespace Benepfist\Xtrabackup\Manager\Console;

use Benepfist\Xtrabackup\Manager\Utils\ParameterParser;
use Benepfist\Xtrabackup\Manager\Utils\FileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IncrementalCommand extends BaseCommand
{
    /**
     * An file FileHelper
     *
     * @var FileHelper
     */
    protected $file;

    /**
     * init command
     *
     * @param Process $process
     * @param string|null  $name
     */
    public function __construct($name = null, FileHelper $file = null)
    {
        $this->file = $file;
        parent::__construct($name);

    }

    /**
     *  configure the incremental backup comand
     *
     * @return  void
     */
    public function configure()
    {
        parent::configure();

        $this->setName('incremental')
             ->setDescription('Create an incremental backup')
             ->addArgument('backup-dir', InputArgument::REQUIRED, 'Backup directory')
             ->addOption('user', null, InputOption::VALUE_OPTIONAL, 'The database user')
             ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'The database password.')
             ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Remote host')
             ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'The database port')
             ->addOption('rsync', null, InputOption::VALUE_NONE, 'Use rsync utiltiy to optimize local file transfer');
    }

    /**
     *  execute the incremental backup command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @return  void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $backup = date('Y_m_d_His');
        $backup_dir = $this->argument('backup-dir');
        $restore_dir = $this->option('restore-dir');
        $base_dir = "{$backup_dir}/base";
        $stack = "{$backup_dir}/stack";
        $backup_path = "{$backup_dir}/{$backup}";
        $releases_dir = "$restore_dir/releases";
        $restore_path = "{$restore_dir}/current";
        $backup_path_inc = "{$backup_path}_inc";

        // Check if there is already a BaseBackup
        if (! $this->baseBackupExists($stack)) {
            // (FALSE) Step 1.1 Create BaseBackup
            $this->takeFullBackup($backup_path);

            $this->copyBackup($backup_path, $releases_dir);

            // Link Backup to restore_path
            //$this->linkDirectory("{$releases_dir}/{$backup}/", "{$restore_path}");
            $this->copyBackup("{$releases_dir}/{$backup}/*", "{$stack}");

            // First Basedir is basebackup
            $this->linkDirectory("{$releases_dir}/{$backup}", "$base_dir");

            // Prepare Base Backup
            $this->applyBaseBackup("{$stack}/");
        }
        
        // Take Incremental Backup
        $this->takeIncrementalBackup("$backup_path_inc/", $base_dir);

        // Optonal Copy Incremental Backup to Release directory
        //$this->copyBackupForQuickRestore($releases_dir, $backup_path_inc);
        
        // Prepare Incremental Backup
        $this->applyIncrementalBackup($stack, $backup_path_inc);
        

        // Symlink new incremental to base
        $this->linkDirectory($backup_path_inc, $base_dir);

        // Copy Stack to current
        $this->copyBackup("$stack/*", $restore_path);

        // Step X) Prepare for Restore
        $this->prepareForRestore($restore_path);

        $this->info("Backup finished!");
    }

    /**
     *  apply incremental bakup
     *
     * @param   string $base_dir
     * @param   string $incremental_dir
     *
     * @return  void
     */
    protected function applyIncrementalBackup($base_dir, $incremental_dir)
    {
        $this->info("Apply incremental backup");
        $this->runProcess("innobackupex --apply-log --redo-only {$base_dir} --incremental-dir={$incremental_dir}");
    }

    /**
     *  apply Base Backup
     *
     * @param   string $base_dir
     *
     * @return  void
     */
    protected function applyBaseBackup($base_dir)
    {
        $this->info("Apply base backup");
        $this->runProcess("innobackupex --apply-log --redo-only $base_dir");
        
    }

    /**
     *  create a incremental backup
     *
     * @param   string $backup
     * @param   string $base_dir
     *
     * @return  void
     */
    protected function takeIncrementalBackup($backup, $base_dir)
    {
        $param = implode(' ', (new ParameterParser)->getParameters($this->input));

        $this->info("Backup database incremental ...");
        $this->runProcess("innobackupex  --incremental {$backup} --incremental-basedir={$base_dir} {$param} --no-timestamp");
    }

    /**
     *  chech if there is already a base backup
     *
     * @param   string $base_dir
     *
     * @return  boolean
     */
    protected function baseBackupExists($base_dir)
    {
        //return is_dir($base_dir);
        return $this->file->isDirectory($base_dir);
    }
}
