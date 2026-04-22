<?php

namespace App\Ai\Tools\FileSystem;

use NeuronAI\Tools\Tool;

class DescribeProjectRootDirectoryTool extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'describe_project_root_directory',
            'Provides a dynamic, structured overview of the project\'s root directory, Dont use for generale purpose directory search'
        );
    }

    /**
     * Implementing the tool logic
     */
    public function __invoke(): string
    {
        $root = getcwd();
        return $this->getTree($root);
    }

    protected function getTree(string $root): string
    {
        $tree = [];
        $excludes = ['.git', '.idea', '.', '..', '.env'];
        $iterator = new \DirectoryIterator($root);

        foreach ($iterator as $fileInfo) {
            if (!in_array($fileInfo->getFilename(), $excludes)) {
                $name = $fileInfo->getFilename();
                $isDir = $fileInfo->isDir();

                $tree[] = $name . ($isDir ? '/' : '');
            }
        }
        $output = "project-root/" . PHP_EOL;

        foreach ($tree as $i => $item) {
            $prefix = ($i == count($tree) - 1) ? "└── " : "├── ";
            $output .= $prefix . $item . PHP_EOL;
        }

        return $output;
    }
}
