<?php

namespace App\Ai\Tool;

use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\PropertyType;
use App\Ai\Tool\Traits\ValidatesPath;
use Symfony\Component\Finder\Finder;
use function getcwd;
use function array_map;
use function explode;
use function stripos;
use function str_replace;
use function substr;
use function trim;
use function implode;
use function file_get_contents;

class SearchFiles extends Tool
{
    use ValidatesPath;

    public function __construct()
    {
        parent::__construct(
            'search_files',
            'Search for files containing specific text in the project directory.'
        );
    }

    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'pattern',
                type: PropertyType::STRING,
                description: 'Text pattern to search for in file contents',
                required: true
            ),
            new ToolProperty(
                name: 'directory',
                type: PropertyType::STRING,
                description: 'Optional directory to search in (defaults to project root)',
                required: false
            ),
            new ToolProperty(
                name: 'types',
                type: PropertyType::STRING,
                description: 'Optional file types to search (comma separated: php,js,html,css,txt)',
                required: false
            )
        ];
    }

    public function __invoke(string $pattern, string $directory = null, string $types = null): string
    {
        $basePath = getcwd();

        if ($directory) {
            $basePath = $this->validatePath($directory);
        }

        $finder = Finder::create()
            ->in($basePath)
            ->files();

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
            $filepath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $content = file_get_contents($file->getRealPath());

            if (stripos($content, $pattern) !== false) {
                $results[] = "File: {$filepath}\n  Content: " . substr($content, stripos($content, $pattern), 100) . '...';
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
    }
}