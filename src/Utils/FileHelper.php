<?php

namespace Benepfist\Xtrabackup\Manager\Utils;

class FileHelper
{

    /**
     * check if directory exists
     *
     * @param   string  $path
     *
     * @return  boolean
     */
    public function isDirectory($path)
    {
        return is_dir($path);
    }
}
