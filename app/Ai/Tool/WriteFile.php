<?php

namespace App\Ai\Tool;

use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\PropertyType;
use App\Ai\Tool\Traits\ValidatesPath;
use function dirname;
use function is_dir;
use function mkdir;
use function is_writable;
use function file_put_contents;

class WriteFile extends Tool
{
    use ValidatesPath;

    public function __construct()
    {
        parent::__construct(
            'write_file',
            'Write or overwrite the contents of a file in the project directory.'
        );
    }

    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'filepath',
                type: PropertyType::STRING,
                description: 'Path to the file relative to the project root',
                required: true
            ),
            new ToolProperty(
                name: 'content',
                type: PropertyType::STRING,
                description: 'Content to write to the file',
                required: true
            )
        ];
    }

    public function __invoke(string $filepath, string $content): string
    {
        $fullPath = $this->validatePath($filepath);
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
}