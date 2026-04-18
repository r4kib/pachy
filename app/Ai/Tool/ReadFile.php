<?php

namespace App\Ai\Tool;

use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\PropertyType;
use App\Ai\Tool\Traits\ValidatesPath;
use function file_exists;
use function is_file;
use function is_readable;
use function file_get_contents;

class ReadFile extends Tool
{
    use ValidatesPath;

    public function __construct()
    {
        parent::__construct(
            'read_file',
            'Read the contents of a file from the project directory.'
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
            )
        ];
    }

    public function __invoke(string $filepath): string
    {
        $fullPath = $this->validatePath($filepath);

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            return "Error: File not found at path '{$filepath}'";
        }
        if (!is_readable($fullPath)) {
            return "Error: Cannot read file at path '{$filepath}'";
        }
        return file_get_contents($fullPath);
    }
}