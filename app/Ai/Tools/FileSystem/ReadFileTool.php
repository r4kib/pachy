<?php

declare(strict_types=1);

namespace App\Ai\Tools\FileSystem;

use App\Ai\Tools\BaseTool;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

use function file_get_contents;
use function is_file;
use function is_readable;
use function mb_strlen;

class ReadFileTool extends BaseTool
{
    public function __construct()
    {
        parent::__construct(
            name: 'read_file',
            description: 'Read the contents of a text file.',
        );
    }

    protected function properties(): array
    {
        return [
            ToolProperty::make(
                name: 'file_path',
                type: PropertyType::STRING,
                description: 'Path to the file to read.',
            ),
        ];
    }

    public function __invoke(string $file_path): string
    {
        if (! is_file($file_path)) {
            return "Error: File '{$file_path}' does not exist.";
        }

        return match (is_readable($file_path)) {
            false => "Error: File '{$file_path}' is not readable.",
            default => $this->readFile($file_path),
        };
    }

    private function readFile(string $path): string
    {
        $content = file_get_contents($path);

        return match ($content) {
            false => "Error: Unable to read file '{$path}'.",
            default => "{$content}\n\n[File read successfully: ".mb_strlen($content).' characters]',
        };
    }
}
