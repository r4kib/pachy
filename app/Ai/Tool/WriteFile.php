<?php

namespace App\Ai\Tool;

use NeuronAI\Agent\Tool;
use NeuronAI\Agent\ToolProperty;
use function getcwd;
use function realpath;
use function strpos;
use function dirname;
use function file_put_contents;

class WriteFile extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'write_file',
            'Write or overwrite the contents of a file in the project directory.'
        );

        $this->addProperty(
            ToolProperty::make('filepath', 'STRING', 'Path to the file relative to the project root (no leading slashes)', true)
        );

        $this->addProperty(
            ToolProperty::make('content', 'STRING', 'Content to write to the file', true)
        );
    }

    public function run(array $args): string
    {
        return $this->save($args['filepath'], $args['content']);
    }

    public function save(string $filepath, string $content): string
    {
        $fullPath = $this->validate($filepath);
        $directory = dirname($fullPath);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return "Error: Cannot create directory '{$directory}'";
        }

        if (!is_writable($directory) && !is_writable($fullPath)) {
            return "Error: Cannot write to file at path '{$filepath}'";
        }

        if (file_put_contents($fullPath, $content) === false) {
            return "Error: Failed to write to file at path '{$filepath}'";
        }

        return "Successfully wrote content to file at '{$filepath}'";
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

        return $fullPath;
    }
}