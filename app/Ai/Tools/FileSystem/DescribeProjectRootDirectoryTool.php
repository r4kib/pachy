<?php

namespace App\Ai\Tools\FileSystem;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class DescribeProjectRootDirectoryTool extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'describe_project_root_directory',
            'Provides a dynamic, structured overview of the project\'s root directory, Dont use for generale purpose directory search'
        );
    }

    protected function properties(): array
    {
        return [
            ToolProperty::make(
                name: 'level',
                type: PropertyType::INTEGER,
                description: 'The depth of the directory tree to display (default: 1).',
            ),
            ToolProperty::make(
                name: 'exclude_dependencies',
                type: PropertyType::BOOLEAN,
                description: 'Whether to exclude package manager directories like vendor, node_modules, etc. (default: true).',
            ),
        ];
    }

    /**
     * Implementing the tool logic
     */
    public function __invoke(?int $level = 1, ?bool $exclude_dependencies = true): string
    {
        $level ??= 1;
        $exclude_dependencies ??= true;
        $root = getcwd();

        return $this->getTree($root, $level, $exclude_dependencies);
    }

    protected function getTree(string $root, int $maxLevel, bool $excludeDependencies): string
    {
        $output = 'project-root/'.PHP_EOL;
        $this->buildTree($root, 0, $maxLevel, $excludeDependencies, '', $output);

        return $output;
    }

    private function buildTree(string $dir, int $currentLevel, int $maxLevel, bool $excludeDependencies, string $prefix, string &$output): void
    {
        if ($currentLevel >= $maxLevel) return;

        if (! is_dir($dir)) return;

        $excludes = ['.git', '.idea', '.', '..', '.env', '.pachy'];
        if ($excludeDependencies) {
            $excludes = array_merge($excludes, ['vendor', 'node_modules', 'bower_components']);
        }

        $items = [];

        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $fileInfo) {
            if (in_array($fileInfo->getFilename(), $excludes)) {
                continue;
            }
            $items[] = [
                'name' => $fileInfo->getFilename(),
                'isDir' => $fileInfo->isDir(),
                'path' => $fileInfo->getPathname(),
            ];
        }

        // Sort items: directories first, then files, then alphabetically
        usort($items, function ($a, $b) {
            if ($a['isDir'] && ! $b['isDir']) {
                return -1;
            }
            if (! $a['isDir'] && $b['isDir']) {
                return 1;
            }

            return strcmp($a['name'], $b['name']);
        });

        $count = count($items);
        foreach ($items as $i => $item) {
            $isLast = ($i === $count - 1);
            $name = $item['name'];
            $isDir = $item['isDir'];

            $output .= $prefix.($isLast ? '└── ' : '├── ').$name.($isDir ? '/' : '').PHP_EOL;

            if ($isDir) {
                $newPrefix = $prefix.($isLast ? '    ' : '│   ');
                $this->buildTree($item['path'], $currentLevel + 1, $maxLevel, $excludeDependencies, $newPrefix, $output);
            }
        }
    }
}
