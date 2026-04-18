<?php

namespace App\Ai\Tool;

use NeuronAI\Agent\Tool;
use NeuronAI\Agent\ToolProperty;
use Symfony\Component\Finder\Finder;
use function array_map;
use function explode;
use function preg_match;
use function stripos;
use function str_replace;
use function substr;
use function trim;
use function implode;

class SearchFiles extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'search_files',
            'Search for files containing specific text in the project directory.'
        );

        $this->addProperty(
            ToolProperty::make('pattern', 'STRING', 'Text pattern to search for in file contents', true)
        );

        $this->addProperty(
            ToolProperty::make('directory', 'STRING', 'Optional directory to search in (defaults to project root)', false)
        );

        $this->addProperty(
            ToolProperty::make('types', 'STRING', 'Optional file types to search (comma separated: php,js,html,css,txt)', false)
        );
    }

    public function run(array $args): string
    {
        return $this->search(
            $args['pattern'],
            $args['directory'] ?? null,
            $args['types'] ?? null
        );
    }

    public function search(string $pattern, string $directory = null, string $types = null): string
    {
        $basePath = $directory ?: getcwd();

        if ($directory) {
            $basePath = $this->validate($directory);
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