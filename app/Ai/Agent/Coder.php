<?php

namespace App\Ai\Agent;

use NeuronAI\Agent\Agent;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use Symfony\Component\Finder\Finder;

class Coder extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new Gemini(
            key: env('GEMINI_API_KEY'),
            model: env('GEMINI_MODEL', 'gemini-2.0-flash-exp'),
        );
    }

    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                "You are an expert AI Coder specialized in PHP and JavaScript web development.",
                "You are proficient in modern web technologies, frameworks, and best practices.",
                "You help developers write clean, efficient, and maintainable code.",
                "You have deep knowledge of REST APIs, databases, authentication, and security.",
                "You are familiar with popular frameworks like Laravel, Symfony, React, and Vue.js."
            ],
            steps: [
                "Analyze the user's coding request carefully to understand requirements and constraints.",
                "Write or modify code according to best practices and common patterns.",
                "Provide clear documentation and comments for complex logic.",
                "Suggest optimizations and improvements when applicable.",
                "Ask clarifying questions if requirements are ambiguous or incomplete."
            ],
            output: [
                "Write clean, idiomatic PHP and JavaScript code with proper formatting.",
                "Include relevant examples and usage comments.",
                "Output complete, functional code when applicable.",
                "Explain your approach and reasoning when helpful.",
                "Use Markdown for code blocks and structured output.",
                "For file-related operations, read or write files in the project directory only."
            ]
        );
    }

    protected function tools(): array
    {
        return [
            Tool::make(
                'read_file',
                'Read the contents of a file from the project directory.'
            )
                ->addProperty(
                    new ToolProperty(
                        name: 'filepath',
                        type: PropertyType::STRING,
                        description: 'Path to the file relative to the project root (no leading slashes)',
                        required: true
                    )
                )
                ->setCallable(function (string $filepath): string {
                    $fullPath = $this->validateFilePath($filepath);
                    if (!file_exists($fullPath) || !is_file($fullPath)) {
                        return "Error: File not found at path '{$filepath}'";
                    }
                    if (!is_readable($fullPath)) {
                        return "Error: Cannot read file at path '{$filepath}'";
                    }
                    return file_get_contents($fullPath);
                }),

            Tool::make(
                'write_file',
                'Write or overwrite the contents of a file in the project directory.'
            )
                ->addProperty(
                    new ToolProperty(
                        name: 'filepath',
                        type: PropertyType::STRING,
                        description: 'Path to the file relative to the project root (no leading slashes)',
                        required: true
                    )
                )
                ->addProperty(
                    new ToolProperty(
                        name: 'content',
                        type: PropertyType::STRING,
                        description: 'Content to write to the file',
                        required: true
                    )
                )
                ->setCallable(function (string $filepath, string $content): string {
                    $fullPath = $this->validateFilePath($filepath);
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
                }),

            Tool::make(
                'search_files',
                'Search for files containing specific text in the project directory.'
            )
                ->addProperty(
                    new ToolProperty(
                        name: 'pattern',
                        type: PropertyType::STRING,
                        description: 'Text pattern to search for in file contents',
                        required: true
                    )
                )
                ->addProperty(
                    new ToolProperty(
                        name: 'directory',
                        type: PropertyType::STRING,
                        description: 'Optional directory to search in (defaults to project root)',
                        required: false
                    )
                )
                ->addProperty(
                    new ToolProperty(
                        name: 'types',
                        type: PropertyType::STRING,
                        description: 'Optional file types to search (comma separated: php,js,html,css,txt)',
                        required: false
                    )
                )
                ->setCallable(function (string $pattern, string $directory = null, string $types = null): string {
                    $basePath = $directory ?: getcwd();

                    $finder = Finder::create()
                        ->in($basePath)
                        ->files();

                    if ($directory) {
                        $finder->in($this->validateFilePath($directory));
                    }

                    if ($types) {
                        $extensions = array_map('trim', explode(',', $types));
                        foreach ($extensions as $ext) {
                            $finder->name("*{$ext}");
                        }
                    } else {
                        $finder->name('*.php')->name('*.js')->name('*.html')->name('*.css')->name('*.txt');
                    }

                    $results = [];
                    $count = 0;

                    foreach ($finder as $file) {
                        $filepath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                        $content = file_get_contents($file->getRealPath());

                        if (stripos($content, $pattern) !== false) {
                            $results[] = "File: {$filepath}\n  Content: " . substr($content, strpos($content, $pattern), 100) . '...';
                            $count++;

                            if ($count >= 20) {
                                break;
                            }
                        }
                    }

                    if (empty($results)) {
                        return "No files found containing pattern '{$pattern}' in the specified directory.";
                    }

                    $output = "Found {$count} files with pattern '{$pattern}':\n\n" . implode("\n---\n\n", $results);
                    return $output;
                }),

            Tool::make(
                'grep_files',
                'Search for text patterns in files using regex capture.'
            )
                ->addProperty(
                    new ToolProperty(
                        name: 'pattern',
                        type: PropertyType::STRING,
                        description: 'Regular expression pattern to search for',
                        required: true
                    )
                )
                ->addProperty(
                    new ToolProperty(
                        name: 'directory',
                        type: PropertyType::STRING,
                        description: 'Optional directory to search in (defaults to project root)',
                        required: false
                    )
                )
                ->addProperty(
                    new ToolProperty(
                        name: 'output_format',
                        type: PropertyType::STRING,
                        description: 'Format: "lines" (show matching lines), "files" (show matching files only)',
                        required: false
                    )
                )
                ->setCallable(function (string $pattern, string $directory = null, string $output_format = 'lines'): string {
                    $basePath = $directory ?: getcwd();

                    $finder = Finder::create()
                        ->in($basePath)
                        ->files()
                        ->name('*.php')->name('*.js')->name('*.html')->name('*.css')->name('*.txt');

                    $results = [];
                    $lineCount = 0;

                    foreach ($finder as $file) {
                        $filepath = $file->getRealPath();
                        $content = file_get_contents($filepath);
                        $lines = explode("\n", $content);

                        $matches = [];
                        foreach ($lines as $lineNum => $line) {
                            if (preg_match($pattern, $line, $matches)) {
                                $lineInfo = "Line {$lineNum}: " . $matches[0];
                                if (count($matches) > 1) {
                                    $lineInfo .= ' [matches: ' . implode(', ', $matches) . ']';
                                }
                                $matches[] = $lineInfo;
                            }
                        }

                        if (!empty($matches)) {
                            if (strtolower($output_format) === 'files') {
                                $results[] = "File: ".str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $filepath);
                            } else {
                                $relativePath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $filepath);
                                $results[] = "File: {$relativePath}\n" . implode("\n", array_slice(array_unique($matches), 0, 10));
                            }
                            $lineCount += count($matches);

                            if ($lineCount >= 100) {
                                $results[] = "\n... (truncated: too many matching lines)";
                                break;
                            }
                        }
                    }

                    if (empty($results)) {
                        return "No matches found for pattern '{$pattern}' in the specified directory.";
                    }

                    $output = "Found " . count($results) . " matching file(s) with {$lineCount} total matches:\n\n" .
                               implode("\n\n---\n\n", $results);
                    return $output;
                })
        ];
    }

    private function validateFilePath(string $path): string
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