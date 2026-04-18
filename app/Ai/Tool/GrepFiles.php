<?php

namespace App\Ai\Tool;

use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\PropertyType;
use App\Ai\Tool\Traits\ValidatesPath;
use Symfony\Component\Finder\Finder;
use function getcwd;
use function explode;
use function implode;
use function preg_match;
use function str_replace;
use function array_slice;
use function array_unique;
use function file_get_contents;
use function strtolower;

class GrepFiles extends Tool
{
    use ValidatesPath;

    public function __construct()
    {
        parent::__construct(
            'grep_files',
            'Search for text patterns in files using regex capture.'
        );
    }

    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'pattern',
                type: PropertyType::STRING,
                description: 'Regular expression pattern to search for',
                required: true
            ),
            new ToolProperty(
                name: 'directory',
                type: PropertyType::STRING,
                description: 'Optional directory to search in (defaults to project root)',
                required: false
            ),
            new ToolProperty(
                name: 'output_format',
                type: PropertyType::STRING,
                description: 'Format: "lines" (show matching lines), "files" (show matching files only)',
                required: false
            )
        ];
    }

    public function __invoke(string $pattern, string $directory = null, string $output_format = 'lines'): string
    {
        $basePath = getcwd();

        if ($directory) {
            $basePath = $this->validatePath($directory);
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
}