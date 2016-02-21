<?php

namespace Testing;

use Benepfist\Xtrabackup\Manager\Console\IncrementalCommand;
use Benepfist\Xtrabackup\Manager\Utils\FileHelper;
use Mockery as m;

class IncrementalBackupCommandTest extends CommandTest
{
    /**
     *  filehelper
     *
     * @var FileHelper
     */
    private $filehelper;

    /**
     * prepare test
     */
    public function setUp()
    {
        $this->filehelper = m::mock(FileHelper::class);
        $command = new IncrementalCommand(null, $this->filehelper);
        parent::setUp($command, 'incremental');
    }

    /**
     * test if a full backup is takben before the first incremental backup is taken
     *
     * @test
     * @return void
     */
    public function createIncrementalBackupWithoutBase()
    {
        $user = 'homestead';
        $password = 'secret';
        $backup_dir = '/var/backups';

        $this->filehelper->shouldReceive('isDirectory')->andReturn(false);

        $this->executeCommand(['backup-dir' => $backup_dir, '--user' => $user, '--password' => $password], 'Backup finished!');

        $backup_dir = preg_quote($backup_dir, "/");
        $this->assertCommandDisplays([
            "Backup database",
            "innobackupex {$backup_dir}\/\d{4}_\d{2}_\d{2}_\d{6}\/ --user={$user} --password={$password} --no-timestamp",

            //"ln -nfs {$backup_dir}\/restore\/releases\/{$backup_dir} {$backup_dir}\/base",
            "Apply base backup",
            "innobackupex --apply-log --redo-only {$backup_dir}\/stack",

            "Backup database incremental",
            "innobackupex --apply-log --redo-only {$backup_dir}\/stack --incremental-dir={$backup_dir}\/\d{4}_\d{2}_\d{2}_\d{6}_inc",

            "Apply incremental backup",
            "innobackupex --apply-log --redo-only {$backup_dir}\/stack --incremental-dir={$backup_dir}\/\d{4}_\d{2}_\d{2}_\d{6}_inc",

            "Prepare backup for restore",
            "innobackupex --apply-log {$backup_dir}\/restore\/current",
        ]);
    }

    /**
     * test if further incremental backups are taken
     *
     * @test
     * @return void
     */
    public function createIncrementalBackupWithBase()
    {
        $user = 'homestead';
        $password = 'secret';
        $backup_dir = '/var/backups';

        $this->filehelper->shouldReceive('isDirectory')->andReturn(true);

        $this->executeCommand(['backup-dir' => $backup_dir, '--user' => $user, '--password' => $password], 'Backup finished!');

        $backup_dir = preg_quote($backup_dir, "/");
        $this->assertCommandDoesNotDisplays([
            "Backup database",
            //"innobackupex {$backup_dir}\/\d{4}_\d{2}_\d{2}_\d{6}\/ --user={$user} --password={$password} --no-timestamp",
        ]);

        $this->assertCommandDisplays([
            "Backup database incremental",
            "innobackupex  --incremental {$backup_dir}\/\d{4}_\d{2}_\d{2}_\d{6}_inc\/ --incremental-basedir={$backup_dir}\/base --user={$user} --password={$password} --no-timestamp",
            
            "Apply incremental backup",
            "innobackupex --apply-log --redo-only {$backup_dir}\/stack --incremental-dir={$backup_dir}\/\d{4}_\d{2}_\d{2}_\d{6}_inc",

            "Prepare backup for restore",
            "innobackupex --apply-log $backup_dir\/restore\/current",
        ]);
    }
}
