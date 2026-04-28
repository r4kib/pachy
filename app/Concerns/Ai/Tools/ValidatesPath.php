<?php

namespace App\Concerns\Ai\Tools;

use function getcwd;
use function realpath;
use function strpos;

trait ValidatesPath
{
    protected function validatePath(string $path): string
    {
        $fullPath = realpath(getcwd().DIRECTORY_SEPARATOR.$path);
        $projectPath = realpath(getcwd());

        if ($fullPath === false || strpos($fullPath.DIRECTORY_SEPARATOR, $projectPath.DIRECTORY_SEPARATOR) === false) {
            throw new \Exception('Security: Attempted to access file outside project directory');
        }

        return $fullPath;
    }
}
