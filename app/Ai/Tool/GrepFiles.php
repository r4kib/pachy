<?php

namespace App\Ai\Tool;

use NeuronAI\Agent\Tool;
use NeuronAI\Agent\ToolProperty;
use Symfony\Component\Finder\Finder;
use function explode;
use function implode;
use function preg_match;
use function str_replace;

class GrepFiles extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'grep_files',
            'Search for text patterns in files using regex capture.'
        );

        $this->addProperty(
            ToolProperty::make('pattern', 'STRING', 'Regular expression pattern to search for', true)
        );

        $this->addProperty(
            ToolProperty::make('directory', 'STRING', 'Optional directory to search in (defaults to project root)', false)
        );

        $this->addProperty(
            ToolProperty::make('output_format', 'STRING', 'Format: "lines" (show matching lines), "files" (show matching files only)', false)
        );
    }

    public function run(array $args): string
    {
        return $this->grep(
            $args['pattern'],
            $args['directory'] ?? null,
            $args['output_format'] ?? 'lines'
        );
    }

    public function grep(string $pattern, string $directory = null, string $output_format = 'lines'): string
    {
        $basePath = $directory ?: getcwd();

        if ($directory) {
            $basePath = $this->validate($directory);
        }

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