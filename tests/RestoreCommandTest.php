<?php

namespace Testing;

use Benepfist\Xtrabackup\Manager\Console\RestoreCommand;

class RestoreCommandTest extends CommandTest
{
    /**
     * prepare test
     */
    public function setUp()
    {
        parent::setUp(RestoreCommand::class, 'restore');
    }

    /**
     * test if command gets executet;
     *
     * @test
     * @return void
     */
    public function executeRestoreCommand()
    {
        $restore_dir = '/var/backups/';
        $this->executeCommand(['--restore-dir' => $restore_dir], 'Restore backup ...');

        $restore_dir = preg_quote($restore_dir, "/");

        $this->assertCommandDisplays([
            'Backup datadir',
            "innobackupex --copy-back $restore_dir",
            'Restore backup',
            'Modify Permission',
            'sudo chown -R mysql: \/var\/lib\/mysql',
            'Restart mysql',
            'sudo service mysql start',
        ]);
    }
}
