<?php

namespace Testing;

use Mockery as m;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * tested command
     *
     * @var Symfony\Component\Console\Command\Command
     */
    protected $command;

    /**
     * command tester
     *
     * @var Symfony\Component\Console\Tester\CommandTester
     */
    protected $commandTester;

    /**
     * prepare test
     *
     * @param string $command
     * @param string $commandName
     */
    public function setUp($command, $commandName)
    {
        parent::setUp();

        $application = new Application;
        $application->add(new $command());

        $this->command = $application->find($commandName);
        $this->commandTester = new CommandTester($this->command);
        
    }

    /**
     * test helper: assert Command Output
     *
     * @param  string $regexpressions
     *
     * @return void
     */
    protected function assertCommandDisplays($regexpressions)
    {
        if (is_array($regexpressions)) {
            foreach ($regexpressions as $regexp) {
                $this->assertCommandDisplays($regexp);
            }
        } else {
            $this->assertRegExp("/$regexpressions/", $this->commandTester->getDisplay(true));
        }
    }
    
    /**
     * test helper: execute command
     *
     * @param  array $parameters
     * @param  string $output
     *
     * @return void
     */
    protected function executeCommand(array $parameters = [], $output = "")
    {
        $this->commandTester->execute(array_merge(['command' => $this->command->getname(), '--debug' => true], $parameters));

        //echo "\n".$this->commandTester->getDisplay(true);
        $this->assertCommandDisplays($output);
    }

    /**
     * close mockery
     *
     * @return void
     */
    public function tearDown()
    {
        m::close();
    }
}
