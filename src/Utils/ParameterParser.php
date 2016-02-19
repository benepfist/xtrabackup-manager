<?php

namespace Benepfist\Xtrabackup\Manager\Utils;

use Symfony\Component\Console\Input\InputInterface;

class ParameterParser
{
    private $escapedCommands = ["command", "-vvv", "-vv", "-v", "-h", "-n", "-q", "--help", "--quiet", "--version", "--ansi", "--no-ansi", "--no-interaction", "--verbose", "--debug", "debug", "backup-dir", "restore-dir", "--backup-dir", "--restore-dir"];

    /**
     * retrieve parameters for process
     *
     * @param  InputInterface $input
     *
     * @return array
     */
    public function getParameters(InputInterface $input)
    {
        $parameters = $this->flattenParameter($input->getArguments());

        return array_merge($parameters, $this->flattenParameter($input->getOptions(), '--'));
    }

    /**
     * parse argument and options for command
     *
     * @param  array $values
     * @param  string $prefix
     *
     * @return array
     */
    private function flattenParameter($values, $prefix = '')
    {
        $parameters = [];
        foreach ($values as $key => $value) {
            if ($this->escapeCommand($key, $value)) {
                // ['option' => true] ==> "--option"
                if (is_bool($value)) {
                    $parameters[] = "{$prefix}{$key}";
                } else { // ['option' => 'value'] => "--option=value"
                    $parameters[] = "{$prefix}{$key}={$value}";
                }
            }
        }

        return $parameters;
    }

    /**
     * escape command options
     *
     * @param  string $key
     * @param  string $value
     *
     * @return boolean
     */
    private function escapeCommand($key, $value)
    {
        if ($value === null || $value === false || in_array($key, $this->escapedCommands) || in_array((string) $value, $this->escapedCommands)) {
            return false;
        }
        return true;
    }
}
