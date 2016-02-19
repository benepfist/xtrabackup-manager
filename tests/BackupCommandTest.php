<?php

namespace Testing;

use Benepfist\Xtrabackup\Manager\Console\BackupCommand;

class BackupCommandTest extends CommandTest
{

    /**
     * prepare test
     */
    public function setUp()
    {
        parent::setUp(BackupCommand::class, 'backup:full');
    }

    /**
     * test if command gets executet;
     *
     * @test
     * @return void
     */
    public function executeFullBackupWithoutCredentials()
    {
        $backup_dir = '/var/backups/';
        $this->executeCommand(['backup-dir' => '/var/backups/'], 'Backup finished!');

        $backup_dir = preg_quote($backup_dir, "/");
        $this->assertCommandDisplays([
            "Backup database",
            "innobackupex $backup_dir\d{4}_\d{2}_\d{2}_\d{6}\/  --no-timestamp",
            "Copy backup for quick restore",
            "Prepare backup for restore",
            "innobackupex --apply-log $backup_dir"."restore\/current",
        ]);
    }

    /**
     * execute command with credentials
     *
     * @test
     * @return void
     */
    public function executeFullBackupWithCredentials()
    {
        $backup_dir = '/var/backups/';
        $user = 'homestead';
        $password = 'secret';

        $this->executeCommand(['backup-dir' => $backup_dir, '--user' => $user, '--password' => $password], 'Backup finished!');

        $backup_dir = preg_quote($backup_dir, "/");
        $this->assertCommandDisplays([
            "Backup database",
            "innobackupex $backup_dir\d{4}_\d{2}_\d{2}_\d{6}\/ --user=$user --password=$password --no-timestamp",
            "Copy backup for quick restore",
            "Prepare backup for restore",
            "innobackupex --apply-log $backup_dir"."restore\/current",
        ]);
    }
}
