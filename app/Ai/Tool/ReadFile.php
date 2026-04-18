<?php

namespace App\Ai\Tool;

use NeuronAI\Agent\Tool;
use NeuronAI\Agent\ToolProperty;
use function getcwd;
use function realpath;
use function strpos;

class ReadFile extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'read_file',
            'Read the contents of a file from the project directory.'
        );

        $this->addProperty(
            ToolProperty::make('filepath', 'STRING', 'Path to the file relative to the project root (no leading slashes)', true)
        );
    }

    public function run(array $args): string
    {
        return $this->validate($args['filepath']);
    }

    public function validate(string $path): string
    {
        $fullPath = realpath(getcwd() . DIRECTORY_SEPARATOR . $path);

        if ($fullPath === false) {
            return getcwd() . DIRECTORY_SEPARATOR . $path;
        }

        $projectPath = realpath(getcwd());
        if (strpos($fullPath . DIRECTORY_SEPARATOR, $projectPath . DIRECTORY_SEPARATOR) === false) {
            throw new \Exception("Security: Attempted to access file outside project directory");
        }

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            return "Error: File not found at path '{$path}'";
        }
        if (!is_readable($fullPath)) {
            return "Error: Cannot read file at path '{$path}'";
        }
        return file_get_contents($fullPath);
    }
}