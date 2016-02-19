<?php

namespace Testing;

use Benepfist\Xtrabackup\Manager\Utils\ParameterParser;
use Symfony\Component\Console\Input\InputInterface;
use Mockery as m;

class ParameterParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * parameter parser
     *
     * @var Parameterparser
     */
    private $parser;

    /**
     * prepare test
     */
    public function setUp()
    {
        $this->parser = new ParameterParser();
    }

    /**
     * test if a boolean argument or option is correctly parsed
     *
     * @test
     * @return void
     */
    public function parseBooleanParameter()
    {
        // Given I have following Inputs
        $input = $this->getInputMock([], ['rsync' => true]);
        
        // I get following output
        $this->assertEquals(['--rsync'], $this->parser->getParameters($input, '--'));
    }

    /**
     * test if a key value argument is correctly parsed
     *
     * @test
     * @return void
     */
    public function parseKeyValueParameter()
    {
        $input = $this->getInputMock(['key' => 'value']);

        $this->assertEquals(['key=value'], $this->parser->getParameters($input));
    }

    /**
     * test if command argument is escaped
     *
     * @test
     * @return void
     */
    public function escapeCommandArgument()
    {
        $input = $this->getInputMock(['command' => 'test']);

        $this->assertEmpty($this->parser->getParameters($input));
    }

    /**
     * test if verbose level is escaped
     *
     * @test
     * @return void
     */
    public function escapeVerboseArgument()
    {
        $input = $this->getInputMock(['-vvv']);

        $this->assertEmpty($this->parser->getParameters($input));
    }

    /**
     * test if debug option is escaped
     *
     * @test
     * @return void
     */
    public function escapeDebugOption()
    {
        $input = $this->getInputMock(['--debug']);

        $this->assertEmpty($this->parser->getParameters($input));
    }

    /**
     * test if regular option does not get parsed
     *
     * @test
     * @return void
     */
    public function escapeBackupDirOrRestoreDirOption()
    {
        $input = $this->getInputMock(['--backup-dir' => '/var/backups/']);

        $this->assertEmpty($this->parser->getParameters($input));
    }

    /**
     * test if multiple commands are escaped
     *
     * @test
     * @return void
     */
    public function escapeMultipleOptions()
    {
        $input = $this->getInputMock(['--ansi', '--debug', '-v']);

        $output = $this->parser->getParameters($input);
        $this->assertEmpty($output);
    }

    /**
     * helper method: mock InputInterface
     *
     * @param  array  $arguments
     * @param  array  $options
     *
     * @return Mockery
     */
    private function getInputMock($arguments = [], $options = [])
    {
        $input = m::mock(InputInterface::class);
        $input->shouldReceive('getArguments')
              ->andReturn($arguments)
              ->shouldReceive('getOptions')
              ->andReturn($options);
        return $input;
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        m::close();
    }
}
